<?php

namespace App\Http\Requests\Access;

use App\Support\Access\PermissionCatalog;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UpdateRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('roles.update') ?? false;
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('name')) {
            $this->merge([
                'name' => Str::slug($this->string('name')),
            ]);
        } elseif ($this->filled('label')) {
            $this->merge([
                'name' => Str::slug($this->string('label')),
            ]);
        }
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'slug',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.regex' => 'The slug may only contain lowercase letters, numbers, and hyphens.',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $roleId = $this->route('role')?->id;

        return [
            'label' => ['required', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', Rule::unique('roles', 'name')->ignore($roleId)],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', Rule::in(PermissionCatalog::names())],
        ];
    }
}
