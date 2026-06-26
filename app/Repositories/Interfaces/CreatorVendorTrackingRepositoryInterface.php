<?php

namespace App\Repositories\Interfaces;

use App\Models\CreatorVendorTracking;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface CreatorVendorTrackingRepositoryInterface
{
    public function find(int $id): ?CreatorVendorTracking;
    public function findByCreatorAndVendor(int $creatorId, int $vendorId): ?CreatorVendorTracking;
    public function create(array $data): CreatorVendorTracking;
    public function update(int $id, array $data): CreatorVendorTracking;
    public function delete(int $id): bool;

    /**
     * Get active tracking for creator and vendor.
     */
    public function getActiveTrackingForCreatorAndVendor(int $creatorId, int $vendorId): ?CreatorVendorTracking;

    /**
     * Paginate creator vendor tracking records.
     */
    public function paginateForCreator(int $creatorId, int $perPage = 15): LengthAwarePaginator;
}
