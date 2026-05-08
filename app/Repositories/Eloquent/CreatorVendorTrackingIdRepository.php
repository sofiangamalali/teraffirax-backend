<?php

namespace App\Repositories\Eloquent;

use App\Models\CreatorVendorTrackingId;
use App\Repositories\Interfaces\CreatorVendorTrackingIdRepositoryInterface;

class CreatorVendorTrackingIdRepository implements CreatorVendorTrackingIdRepositoryInterface
{
    public function __construct(protected CreatorVendorTrackingId $model)
    {
    }

    public function findByCreatorAndVendor(int $creatorId, int $vendorId): ?CreatorVendorTrackingId
    {
        return $this->model->where('creator_id', $creatorId)
            ->where('vendor_id', $vendorId)
            ->first();
    }

    public function create(array $data): CreatorVendorTrackingId
    {
        return $this->model->create($data);
    }

    public function update(int $id, array $data): CreatorVendorTrackingId
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
