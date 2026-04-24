<?php

namespace App\Repositories\Eloquent;

use App\Models\Otp;
use App\Repositories\Interfaces\OtpRepositoryInterface;



class OtpRepository implements OtpRepositoryInterface
{
    public function __construct(
        protected Otp $model,
    ) {}

    public function upsert(array $where, array $data): Otp
    {
        return $this->model->updateOrCreate($where, $data);
    }

    public function findActiveOtp(string $email, string $type): ?Otp
    {
        return $this->model
            ->where('email', $email)
            ->where('type', $type)
            ->where('is_used', false)
            ->where('expires_at', '>', now())
            ->latest()
            ->first();
    }

    public function delete(int $id): void
    {
        $this->model->destroy($id);
    }

    public function incrementAttempts(int $id): void
    {
        $this->model
            ->where('id', $id)
            ->whereColumn('attempts', '<', 'max_attempts')
            ->increment('attempts');
    }

    public function markAsUsed(int $id): void
    {
        $this->model
            ->where('id', $id)
            ->update([
                'is_used' => true,
            ]);
    }

    public function findOtp(string $email, string $type, string $otp): ?Otp
    {
        return $this->model
            ->where('email', $email)
            ->where('type', $type)
            ->where('otp', $otp)
            ->first();
    }
}
