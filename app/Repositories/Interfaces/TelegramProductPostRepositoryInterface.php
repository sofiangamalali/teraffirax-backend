<?php

namespace App\Repositories\Interfaces;

use App\Models\TelegramProductPost;

interface TelegramProductPostRepositoryInterface
{
    /**
     * Check if there is a recent post for the product.
     */
    public function existsRecentPostForProduct(int $productId, int $socialIntegrationId, int $hours = 3): bool;

    /**
     * Create a new telegram product post record.
     */
    public function createPost(array $data): TelegramProductPost;
}
