<?php

namespace App\Http\Requests\Api\V1\Leads;

use App\Http\Requests\Concerns\PageBuilderFormContract;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreContactRequest extends FormRequest
{
    use PageBuilderFormContract;

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            ...$this->pageBuilderContractRules('contact@1'),
            'contact_name' => ['required', 'string', 'max:255'],
            'contact_phone' => ['nullable', 'required_without:contact_email', 'string', 'max:32', 'regex:/^[0-9+().\s-]{7,32}$/'],
            'contact_email' => ['nullable', 'required_without:contact_phone', 'email:rfc', 'max:255'],
            'company' => ['nullable', 'string', 'max:255'],
            'subject' => ['nullable', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:10000'],
            'consent' => ['required', 'accepted'],
            'privacy_policy_version' => ['required', Rule::in([(string) config('leads.privacy_policy_version')])],
            'website' => ['nullable', 'string', 'max:0'],
        ];
    }
}
