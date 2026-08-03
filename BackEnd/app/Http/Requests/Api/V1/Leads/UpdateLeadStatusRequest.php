<?php

namespace App\Http\Requests\Api\V1\Leads;

use App\Models\Lead;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateLeadStatusRequest extends FormRequest
{
    /** @return array<string, mixed> */
    public function rules(): array
    {
        return ['status' => ['required', Rule::in(Lead::STATUSES)]];
    }
}
