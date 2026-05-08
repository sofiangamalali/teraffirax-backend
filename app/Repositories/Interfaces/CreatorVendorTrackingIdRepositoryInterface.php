<?php

namespace App\Repositories\Interfaces;

use App\Models\CreatorVendorTrackingId;

interface CreatorVendorTrackingIdRepositoryInterface
{
    public function findByCreatorAndVendor(int $creatorId, int $vendorId): ?CreatorVendorTrackingId;
    public function create(array $data): CreatorVendorTrackingId;
    public function update(int $id, array $data): CreatorVendorTrackingId;
    public function delete(int $id): bool;
}
