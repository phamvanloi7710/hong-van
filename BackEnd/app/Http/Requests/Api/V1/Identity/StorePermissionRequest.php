<?php

namespace App\Http\Requests\Api\V1\Identity;

use App\Domain\Identity\PermissionRegistry;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StorePermissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'module' => ['required', 'string', 'max:100', 'regex:/^[a-z][a-z0-9_]*$/'],
            'action' => ['required', 'string', Rule::in(PermissionRegistry::ACTIONS)],
            'name' => ['required', 'string', 'max:160'],
            'description' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
