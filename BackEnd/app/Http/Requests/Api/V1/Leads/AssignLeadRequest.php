<?php

namespace App\Http\Requests\Api\V1\Leads;

use Illuminate\Foundation\Http\FormRequest;

final class AssignLeadRequest extends FormRequest
{
    /** @return array<string, mixed> */
    public function rules(): array
    {
        return ['user_id' => ['nullable', 'string', 'size:26', 'exists:hongvan_users,public_id']];
    }
}
