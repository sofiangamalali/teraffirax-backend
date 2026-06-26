<?php

namespace App\Repositories\Eloquent;

use App\Models\TelegramProductPost;
use App\Repositories\Interfaces\TelegramProductPostRepositoryInterface;

class TelegramProductPostRepository implements TelegramProductPostRepositoryInterface
{
    public function __construct(
        protected TelegramProductPost $model
    ) {}

    /**
     * Check if there is a recent post for the product.
     */
    public function existsRecentPostForProduct(int $productId, int $socialIntegrationId, int $hours = 3): bool
    {
        return $this->model->newQuery()
            ->where('product_id', $productId)
            ->where('social_integration_id', $socialIntegrationId)
            ->where('post_status', 'posted')
            ->where('posted_at', '>', now()->subHours($hours))
            ->lockForUpdate()
            ->exists();
    }

    /**
     * Create a new telegram product post record.
     */
    public function createPost(array $data): TelegramProductPost
    {
        return $this->model->create($data);
    }
}
