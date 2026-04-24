<?php

namespace App\Services;

use App\DTOs\CountryDTO;
use App\Models\Country;
use App\Repositories\Interfaces\CountryRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class CountryService
{
    public function __construct(
        protected CountryRepositoryInterface $countryRepository,
    ) {}

    public function getAllCountries(): Collection
    {
        return $this->countryRepository->all();
    }

    public function getPaginatedCountries(int $perPage = 15): LengthAwarePaginator
    {
        return $this->countryRepository->paginate($perPage);
    }

    public function getAllCountries(): Collection
    {
        return $this->countryRepository->all();
    }

    public function getCountryById(int $id): Country
    {
        return $this->countryRepository->findOrFail($id);
    }

    public function createCountry(CountryDTO $dto): Country
    {
        return $this->countryRepository->create($dto->toArray());
    }

    public function updateCountry(int $id, CountryDTO $dto): Country
    {
        return $this->countryRepository->update($id, $dto->toArray());
    }

    public function deleteCountry(int $id): bool
    {
        return $this->countryRepository->delete($id);
    }
}
