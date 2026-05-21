<?php

namespace App\Http\Requests\Procurement\Projects;

use App\Http\Requests\Procurement\Projects\Concerns\PreparesProjectZoneRows;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProjectRequest extends FormRequest
{
    use PreparesProjectZoneRows;

    public function authorize(): bool
    {
        return $this->user()?->hasPermission('projects.create') ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->prepareZoneRowsForValidation();
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'status' => ['required', 'string', Rule::in(['active', 'inactive'])],
            'zones' => ['nullable', 'array'],
            'zones.*.name' => ['required', 'string', 'max:255'],
            'zones.*.status' => ['required', 'string', Rule::in(['active', 'inactive'])],
        ];
    }
}
