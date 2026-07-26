<?php

namespace App\Http\Requests\Access;

use App\Support\Access\UserDepartment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('users.update') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $userId = $this->route('user')?->id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($userId)],
            'department' => ['required', 'string', Rule::in(array_keys(UserDepartment::options()))],
            'currency_code' => ['nullable', 'string', 'size:3', 'alpha'],
            'daily_receipt_limit' => ['nullable', 'numeric', 'min:0', 'max:999999999.99'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'roles' => ['nullable', 'array'],
            'roles.*' => ['string', Rule::exists('roles', 'name')],
        ];
    }
}
