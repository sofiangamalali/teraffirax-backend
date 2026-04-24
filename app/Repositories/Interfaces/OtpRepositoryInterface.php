<?php

namespace App\Repositories\Interfaces;

use App\Models\Otp;


interface OtpRepositoryInterface
{
    public function upsert(array $where, array $data): Otp;

    public function findActiveOtp(string $email, string $type): ?Otp;

    public function delete(int $id): void;

    public function incrementAttempts(int $id): void;

    public function markAsUsed(int $id): void;

    public function findOtp(string $email, string $type, string $otp): ?Otp;
}