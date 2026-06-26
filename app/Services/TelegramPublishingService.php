<?php

namespace App\Services;

use App\Models\ContentTemplate;
use App\Models\Product;
use App\Repositories\Interfaces\{
    CreatorRepositoryInterface,
    ProductRepositoryInterface,
    CreatorVendorTrackingRepositoryInterface,
    SocialIntegrationRepositoryInterface,
    ContentTemplateRepositoryInterface,
    TelegramProductPostRepositoryInterface
};

class TelegramPublishingService
{
    public function __construct(
        protected CreatorRepositoryInterface $creatorRepository,
        protected ProductRepositoryInterface $productRepository,
        protected CreatorVendorTrackingRepositoryInterface $creatorVendorTrackingRepository,
        protected SocialIntegrationRepositoryInterface $socialIntegrationRepository,
        protected ContentTemplateRepositoryInterface $contentTemplateRepository,
        protected TelegramProductPostRepositoryInterface $telegramProductPostRepository
    ) {}

    /**
     * Select best product for creator from their categories with rotation fallback.
     */
    public function selectProductForCreator(int $creatorId, int $socialIntegrationId, array $excludedProductIds = [], ?int $forcedCategoryId = null): ?array
    {
        $creator = $this->creatorRepository->findOrFail($creatorId);

        if ($forcedCategoryId) {
            $categoryId = $forcedCategoryId;
        } else {
            $category = $creator->categories()
                ->orderByPivot('last_posted_at', 'asc')
                ->first();

            if (!$category) {
                return null;
            }

            $categoryId = $category->id;
        }

        $product = $this->productRepository->findBestProductForCreator($creatorId, $categoryId, $excludedProductIds, $socialIntegrationId);

        if (!$product) {
            // Update last_posted_at for the initial category
            $creator->categories()->updateExistingPivot($categoryId, ['last_posted_at' => now()]);

            $allCategories = $creator->categories()
                ->where('category_id', '!=', $categoryId)
                ->orderByPivot('last_posted_at', 'asc')
                ->get();

            foreach ($allCategories as $cat) {
                $product = $this->productRepository->findBestProductForCreator($creatorId, $cat->id, $excludedProductIds, $socialIntegrationId);
                if ($product) {
                    $categoryId = $cat->id;
                    break;
                }

                // Update last_posted_at for checked category even if empty
                $creator->categories()->updateExistingPivot($cat->id, ['last_posted_at' => now()]);
            }

            if (!$product) {
                return null;
            }
        }

        $vendorAccount = $this->creatorVendorTrackingRepository->getActiveTrackingForCreatorAndVendor($creatorId, $product->vendor_id);

        if (!$vendorAccount) {
            return null;
        }

        return [
            'product' => $product,
            'category_id' => $categoryId,
            'vendor_account' => $vendorAccount,
        ];
    }

    /**
     * Update category last posted at.
     */
    public function updateCategoryLastPostedAt(int $creatorId, int $categoryId): void
    {
        $creator = $this->creatorRepository->findOrFail($creatorId);
        $creator->categories()->updateExistingPivot($categoryId, ['last_posted_at' => now()]);
    }

    /**
     * Update product details like price, score, or status.
     */
    public function updateProductData(int $productId, array $data): void
    {
        $this->productRepository->update($productId, $data);
    }

    /**
     * Find default template for social integration channel.
     */
    public function findDefaultTemplate(int $creatorId, int $socialIntegrationId): ?ContentTemplate
    {
        return $this->contentTemplateRepository->findDefaultTemplate($creatorId, $socialIntegrationId);
    }

    /**
     * Check if a product was posted recently to a channel.
     */
    public function existsRecentPostForProduct(int $productId, int $socialIntegrationId, int $hours = 3): bool
    {
        return $this->telegramProductPostRepository->existsRecentPostForProduct($productId, $socialIntegrationId, $hours);
    }

    /**
     * Record successful post to Telegram.
     */
    public function recordProductPost(int $socialIntegrationId, int $productId): void
    {
        $this->telegramProductPostRepository->createPost([
            'social_integration_id' => $socialIntegrationId,
            'product_id'            => $productId,
            'post_status'           => 'posted',
            'posted_at'             => now(),
        ]);
    }

    /**
     * Deactivate social integration channel.
     */
    public function deactivateIntegration(int $socialIntegrationId): void
    {
        $this->socialIntegrationRepository->updateById($socialIntegrationId, ['status' => 'inactive']);
    }
}
