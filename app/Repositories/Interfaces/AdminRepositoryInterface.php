<?php

namespace App\Repositories\Interfaces;

use App\Models\Admin;

interface AdminRepositoryInterface
{
    /**
     * Find an admin by ID.
     */
    public function find(int $id): ?Admin;

    /**
     * Find an admin by ID or throw exception.
     */
    public function findOrFail(int $id): Admin;

    /**
     * Find an admin by email.
     */
    public function findByEmail(string $email): ?Admin;

    /**
     * Create a new admin.
     */
    public function create(array $data): Admin;

    /**
     * Update an admin by ID.
     */
    public function update(int $id, array $data): Admin;
}
