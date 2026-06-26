<?php

namespace App\Services;

use App\Models\AccountManager;
use App\Repositories\Interfaces\AccountManagerRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AccountManagerService
{
    public function __construct(
        protected AccountManagerRepositoryInterface $accountManagerRepository,
    ) {}

    /**
     * Get all account managers.
     */
    public function getAll(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->accountManagerRepository->all();
    }

    /**
     * Get paginated account managers.
     */
    public function getPaginated(int $perPage = 15): LengthAwarePaginator
    {
        return $this->accountManagerRepository->paginate($perPage);
    }

    /**
     * Get an account manager by ID.
     */
    public function getById(int $id): AccountManager
    {
        return $this->accountManagerRepository->findOrFail($id);
    }

    /**
     * Create a new account manager.
     */
    public function create(array $data): AccountManager
    {
        return DB::transaction(function () use ($data) {
            return $this->accountManagerRepository->create($data);
        });
    }

    /**
     * Update an account manager.
     */
    public function update(int $id, array $data): AccountManager
    {
        return DB::transaction(function () use ($id, $data) {
            return $this->accountManagerRepository->update($id, $data);
        });
    }

    /**
     * Delete an account manager.
     *
     * @throws ValidationException
     */
    public function delete(int $id): bool
    {
        $manager = $this->accountManagerRepository->findOrFail($id);

        if ($manager->creators()->exists()) {
            throw ValidationException::withMessages([
                'id' => [__('messages.account_manager_assigned_to_creators')]
            ]);
        }

        return DB::transaction(function () use ($id) {
            return $this->accountManagerRepository->delete($id);
        });
    }
}
