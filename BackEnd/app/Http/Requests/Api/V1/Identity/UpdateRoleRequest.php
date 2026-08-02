<?php

namespace App\Http\Requests\Api\V1\Identity;

use App\Models\Role;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $role = $this->route('role');

        return [
            'name' => ['sometimes', 'required', 'string', 'max:120'],
            'slug' => ['sometimes', 'required', 'string', 'max:100', 'regex:/^[a-z][a-z0-9_]*$/', Rule::unique('hongvan_roles', 'slug')->ignore($role instanceof Role ? $role->getKey() : null)],
            'description' => ['sometimes', 'nullable', 'string', 'max:2000'],
            'permission_ids' => ['sometimes', 'array'],
            'permission_ids.*' => ['required', 'string', 'distinct', 'exists:hongvan_permissions,public_id'],
        ];
    }
}
