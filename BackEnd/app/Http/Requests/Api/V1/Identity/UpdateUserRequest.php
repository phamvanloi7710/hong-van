<?php

namespace App\Http\Requests\Api\V1\Identity;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $user = $this->route('user');

        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'email' => ['sometimes', 'required', 'email', 'max:255', Rule::unique('hongvan_users', 'email')->ignore($user instanceof User ? $user->getKey() : null)],
            'password' => ['sometimes', 'required', 'string', 'min:12', 'max:4096', 'confirmed'],
            'role_ids' => ['sometimes', 'array'],
            'role_ids.*' => ['required', 'string', 'distinct', 'exists:hongvan_roles,public_id'],
            'permission_overrides' => ['sometimes', 'array'],
            'permission_overrides.*.permission_id' => ['required', 'string', 'distinct', 'exists:hongvan_permissions,public_id'],
            'permission_overrides.*.is_allowed' => ['required', 'boolean'],
        ];
    }
}
