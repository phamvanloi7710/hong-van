<?php

namespace App\Http\Requests\Api\V1\Leads;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateLeadFollowUpRequest extends FormRequest
{
    /** @return array<string, mixed> */
    public function rules(): array
    {
        return ['next_follow_up_at' => ['nullable', 'date']];
    }
}
