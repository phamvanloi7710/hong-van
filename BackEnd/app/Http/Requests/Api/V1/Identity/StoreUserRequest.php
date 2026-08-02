<?php

namespace App\Http\Requests\Api\V1\Identity;

use Illuminate\Foundation\Http\FormRequest;

final class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:hongvan_users,email'],
            'password' => ['required', 'string', 'min:12', 'max:4096', 'confirmed'],
            'is_active' => ['sometimes', 'boolean'],
            'role_ids' => ['sometimes', 'array'],
            'role_ids.*' => ['required', 'string', 'distinct', 'exists:hongvan_roles,public_id'],
            'permission_overrides' => ['sometimes', 'array'],
            'permission_overrides.*.permission_id' => ['required', 'string', 'distinct', 'exists:hongvan_permissions,public_id'],
            'permission_overrides.*.is_allowed' => ['required', 'boolean'],
        ];
    }
}
