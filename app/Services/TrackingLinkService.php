<?php

namespace App\Services;

use App\DTOs\TrackingLinkDTO;
use App\Exceptions\CreatorNotSubscribedToVendorException;
use App\Exceptions\VendorInactiveException;
use App\Models\TrackingLink;
use App\Repositories\Interfaces\TrackingLinkRepositoryInterface;
use App\Repositories\Interfaces\CreatorVendorTrackingIdRepositoryInterface;
use App\Repositories\Interfaces\ProductRepositoryInterface;
use App\Services\VendorResolverService;
use App\Services\Tracking\TrackingUrlGeneratorRegistry;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;


class TrackingLinkService
{
    public function __construct(
        protected TrackingLinkRepositoryInterface $linkRepository,
        protected SlugService $slugService,
        protected CreatorVendorTrackingIdRepositoryInterface $creatorVendorTrackingRepo,
        protected ProductRepositoryInterface $productRepo,
        protected VendorResolverService $vendorResolverService,
        protected TrackingUrlGeneratorRegistry $trackingUrlGeneratorRegistry,
    ) {
    }

    public function getPaginatedLinks(int $perPage = 15): LengthAwarePaginator
    {
        return $this->linkRepository->paginate($perPage);
    }

    /**
     * Create a tracking link and generate its slug.
     */
    public function createTrackingLink(int $creatorId, TrackingLinkDTO $dto): TrackingLink
    {
        return DB::transaction(function () use ($creatorId, $dto) {
            $url = $dto->redirect_url;

            // 1. Find vendor by URL securely
            $vendor = $this->vendorResolverService->resolveVendor($url);

            if (!$vendor->is_active) {
                throw new VendorInactiveException();
            }

            // 2. Check if creator has tracking ID for this vendor
            $trackingRecord = $this->creatorVendorTrackingRepo->findByCreatorAndVendor(
                (int) $creatorId,
                (int) $vendor->id
            );

            if (!$trackingRecord) {
                throw new CreatorNotSubscribedToVendorException();
            }

            // 3. Generate tracking URL using strategy pattern
            $generator = $this->trackingUrlGeneratorRegistry->resolve($vendor);
            $redirectUrl = $generator->generate($url, $trackingRecord->tracking_id);

            // 4. Create the tracking link (Allow duplicates as requested)
            $link = $this->linkRepository->create([
                'creator_id' => $creatorId,
                'redirect_url' => $redirectUrl,
            ]);

            $slug = $this->slugService->generateSlug($link->id);

            return $this->linkRepository->update($link->id, ['slug' => $slug]);
        });
    }

    /**
     * Resolve a slug to a tracking link and increment click count.
     */
    public function resolveSlug(string $slug): ?TrackingLink
    {
        try {
            $id = $this->slugService->decodeSlug($slug);
            $link = $this->linkRepository->find($id);

            if ($link && $link->slug === $slug) {
                $link->incrementClicks();
                return $link;
            }
        } catch (\InvalidArgumentException $e) {
            return null;
        }

        return null;
    }

    public function getLinkById(int $id): TrackingLink
    {
        return $this->linkRepository->findOrFail($id);
    }
}
