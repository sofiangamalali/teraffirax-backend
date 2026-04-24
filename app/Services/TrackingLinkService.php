<?php

namespace App\Services;

use App\DTOs\TrackingLinkDTO;
use App\Models\TrackingLink;
use App\Repositories\Interfaces\TrackingLinkRepositoryInterface;
use Illuminate\Support\Facades\DB;
use App\Exceptions\DuplicateTrackingLinkException;
use Illuminate\Pagination\LengthAwarePaginator;

class TrackingLinkService
{
    public function __construct(protected
        TrackingLinkRepositoryInterface $linkRepository, protected
        SlugService $slugService,
        )
    {
    }

    public function getPaginatedLinks(int $perPage = 15): LengthAwarePaginator
    {
        return $this->linkRepository->paginate($perPage);
    }

    /**
     * Create a tracking link and generate its slug.
     */
    public function createTrackingLink(TrackingLinkDTO $dto): TrackingLink
    {
        return DB::transaction(function () use ($dto) {
            $existing = $this->linkRepository->findByCreatorAndProduct(
                (int)$dto->creator_id,
                (int)$dto->product_id
            );

            if ($existing) {
                throw new DuplicateTrackingLinkException($existing);
            }

            $link = $this->linkRepository->create($dto->toArray());

            $slug = $this->slugService->generateSlug($link->id);

            return $this->linkRepository->update($link->id, ['slug' => $slug]);
        });
    }

    /**
     * Resolve a slug to a tracking link and increment click count.
     */
    public function resolveSlug(string $slug): ?TrackingLink
    {
        // We could decode first to find by ID (faster index) or just use the unique slug index.
        // The user mentioned "how to search for it at redirect time": Decode -> ID.
        try {
            $id = $this->slugService->decodeSlug($slug);
            $link = $this->linkRepository->find($id);

            if ($link && $link->slug === $slug) {
                $link->incrementClicks();
                return $link;
            }
        }
        catch (\InvalidArgumentException $e) {
            return null;
        }

        return null;
    }

    public function getLinkById(int $id): TrackingLink
    {
        return $this->linkRepository->findOrFail($id);
    }

}
