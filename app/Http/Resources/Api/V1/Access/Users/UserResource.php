<?php

namespace App\Http\Resources\Api\V1\Access\Users;

use App\Models\User;
use App\Support\Access\UserDepartment;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin User
 */
class UserResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $roles = $this->relationLoaded('roles')
            ? $this->roles
            : $this->roles()->get();

        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'department' => $this->department,
            'department_label' => UserDepartment::label((string) ($this->department ?? '')),
            'currency_code' => $this->defaultCurrencyCode(),
            'daily_receipt_limit' => $this->dailyReceiptLimitAmount(),
            'is_super_admin' => $this->isSuperAdmin(),
            'roles' => $roles->map(fn ($role) => [
                'name' => $role->name,
                'label' => $role->label,
            ])->values(),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
