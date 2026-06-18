<?php

namespace App\Repositories\Interfaces;

use App\Models\CreatorVendorTracking;

interface CreatorVendorTrackingRepositoryInterface
{
    public function findByCreatorAndVendor(int $creatorId, int $vendorId): ?CreatorVendorTracking;
    public function create(array $data): CreatorVendorTracking;
    public function update(int $id, array $data): CreatorVendorTracking;
    public function delete(int $id): bool;
}
