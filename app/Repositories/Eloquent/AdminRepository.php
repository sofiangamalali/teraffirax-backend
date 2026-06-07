<?php

namespace App\Repositories\Eloquent;

use App\Models\Admin;
use App\Repositories\Interfaces\AdminRepositoryInterface;

class AdminRepository implements AdminRepositoryInterface
{
    public function __construct(
        protected Admin $model,
    ) {}

    /**
     * Find an admin by ID.
     */
    public function find(int $id): ?Admin
    {
        return $this->model->find($id);
    }

    /**
     * Find an admin by ID or throw exception.
     */
    public function findOrFail(int $id): Admin
    {
        return $this->model->findOrFail($id);
    }

    /**
     * Find an admin by email.
     */
    public function findByEmail(string $email): ?Admin
    {
        return $this->model->where('email', $email)->first();
    }

    /**
     * Create a new admin.
     */
    public function create(array $data): Admin
    {
        return $this->model->create($data);
    }

    /**
     * Update an admin by ID.
     */
    public function update(int $id, array $data): Admin
    {
        $admin = $this->findOrFail($id);
        $admin->update($data);
        return $admin->fresh();
    }
}
