<?php

namespace App\Repositories\Eloquent;

use App\Models\CreatorVendorTracking;
use App\Repositories\Interfaces\CreatorVendorTrackingRepositoryInterface;

class CreatorVendorTrackingRepository implements CreatorVendorTrackingRepositoryInterface
{
    public function __construct(protected CreatorVendorTracking $model)
    {
    }

    public function findByCreatorAndVendor(int $creatorId, int $vendorId): ?CreatorVendorTracking
    {
        return $this->model->where('creator_id', $creatorId)
            ->where('vendor_id', $vendorId)
            ->first();
    }

    public function create(array $data): CreatorVendorTracking
    {
        return $this->model->create($data);
    }

    public function update(int $id, array $data): CreatorVendorTracking
    {
        $record = $this->model->findOrFail($id);
        $record->update($data);
        return $record;
    }

    public function delete(int $id): bool
    {
        return $this->model->destroy($id) > 0;
    }
}
