<?php

namespace App\Services;

use App\Models\WithdrawMethod;
use App\Models\CreatorWithdrawMethod;
use App\Models\CreatorWithdrawMethodValue;
use App\Repositories\Interfaces\CreatorWithdrawMethodRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class CreatorWithdrawMethodService
{
    protected CreatorWithdrawMethodRepositoryInterface $repository;
    protected DynamicValidationService $validationService;

    public function __construct(
        CreatorWithdrawMethodRepositoryInterface $repository,
        DynamicValidationService $validationService
    ) {
        $this->repository = $repository;
        $this->validationService = $validationService;
    }

    public function getAllMethods(): Collection
    {
        return $this->repository->all();
    }

    public function getCreatorMethods(int $creatorId): Collection
    {
        return $this->repository->getByCreator($creatorId);
    }

    /**
     * Store a new creator withdrawal method with its dynamic field values.
     *
     * @param array $data
     * @return CreatorWithdrawMethod
     * @throws ValidationException
     */
    public function createMethod(array $data): CreatorWithdrawMethod
    {
        return DB::transaction(function () use ($data) {
            // 1. Get the withdraw method and its fields
            $method = WithdrawMethod::with('fields')->findOrFail($data['withdraw_method_id']);
            
            // 2. Generate dynamic rules and validate the values
            $rules = $this->validationService->convertToLaravelRules($method->fields);
            $values = $data['values'] ?? [];
            
            $validator = Validator::make($values, $rules);
            if ($validator->fails()) {
                throw new ValidationException($validator);
            }

            // 3. Ensure the first method is always default
            $hasMethods = $this->repository->getByCreator($data['creator_id'])->isNotEmpty();
            $isDefault = $data['is_default'] ?? false;
            if (!$hasMethods) {
                $isDefault = true;
            }

            // 4. Create the pivot link
            $creatorMethod = $this->repository->create([
                'creator_id' => $data['creator_id'],
                'withdraw_method_id' => $method->id,
                'is_default' => $isDefault
            ]);

            // 4. Save the field values
            foreach ($values as $fieldName => $fieldValue) {
                CreatorWithdrawMethodValue::create([
                    'creator_withdraw_method_id' => $creatorMethod->id,
                    'field_name' => $fieldName,
                    'field_value' => $fieldValue
                ]);
            }

            if ($creatorMethod->is_default) {
                $this->repository->unsetOtherDefaults($creatorMethod->id, $creatorMethod->creator_id);
            }
            
            return $creatorMethod->load('values');
        });
    }

    public function getMethodById(int $id): ?CreatorWithdrawMethod
    {
        return $this->repository->find($id)?->load(['values', 'withdrawMethod.fields']);
    }

    public function updateMethod(int $id, array $data): bool
    {
        return DB::transaction(function () use ($id, $data) {
            $method = $this->repository->find($id);
            if (!$method) return false;

            // Prevent unsetting default directly
            if (isset($data['is_default']) && !$data['is_default'] && $method->is_default) {
                unset($data['is_default']);
            }

            $updated = $this->repository->update($id, $data);
            
            if ($updated && isset($data['is_default']) && $data['is_default']) {
                $this->repository->unsetOtherDefaults($id, $method->creator_id);
            }
            
            return $updated;
        });
    }

    public function deleteMethod(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            $method = $this->repository->find($id);
            if (!$method) return false;

            $deleted = $this->repository->delete($id);

            // If the deleted method was default, make another one default
            if ($deleted && $method->is_default) {
                $newDefault = $this->repository->getByCreator($method->creator_id)->first();
                if ($newDefault) {
                    $this->repository->update($newDefault->id, ['is_default' => true]);
                }
            }

            return $deleted;
        });
    }
}
