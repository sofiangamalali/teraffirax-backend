<?php

namespace App\Services;

use App\DTOs\VendorDTO;
use App\Models\Vendor;
use App\Repositories\Interfaces\VendorRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class VendorService
{
    public function __construct(
        protected VendorRepositoryInterface $vendorRepository,
    ) {}

    public function getAllVendors(): Collection
    {
        return $this->vendorRepository->all();
    }

    public function getPaginatedVendors(int $perPage = 15): LengthAwarePaginator
    {
        return $this->vendorRepository->paginate($perPage);
    }

    public function getVendorById(int $id): Vendor
    {
        return $this->vendorRepository->findOrFail($id);
    }

    public function createVendor(VendorDTO $dto): Vendor
    {
        return DB::transaction(function () use ($dto) {
            return $this->vendorRepository->create($dto->toArray());
        });
    }

    public function updateVendor(int $id, VendorDTO $dto): Vendor
    {
        return DB::transaction(function () use ($id, $dto) {
            return $this->vendorRepository->update($id, $dto->toArray());
        });
    }

    public function deleteVendor(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            return $this->vendorRepository->delete($id);
        });
    }

    public function getPaginatedVendorsByCountry(int $countryId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->vendorRepository->paginateByCountry($countryId, $perPage);
    }
}
