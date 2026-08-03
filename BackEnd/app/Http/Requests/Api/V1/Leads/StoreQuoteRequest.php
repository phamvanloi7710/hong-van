<?php

namespace App\Http\Requests\Api\V1\Leads;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreQuoteRequest extends FormRequest
{
    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'contact_name' => ['required', 'string', 'max:255'],
            'contact_phone' => ['required', 'string', 'max:32', 'regex:/^[0-9+().\s-]{7,32}$/'],
            'contact_email' => ['nullable', 'email:rfc', 'max:255'],
            'message' => ['nullable', 'string', 'max:10000'],
            'items' => ['required', 'array', 'min:1', 'max:30'],
            'items.*.product_id' => ['required', 'string', 'size:26', Rule::exists('hongvan_products', 'public_id')->where(fn ($query) => $query->where('status', 'published')->whereNull('deleted_at'))],
            'items.*.quantity' => ['nullable', 'numeric', 'gt:0'],
            'items.*.unit' => ['nullable', 'string', 'max:32'],
            'items.*.notes' => ['nullable', 'string', 'max:2000'],
            'consent' => ['required', 'accepted'],
            'privacy_policy_version' => ['required', Rule::in([(string) config('leads.privacy_policy_version')])],
            'website' => ['nullable', 'string', 'max:0'],
        ];
    }
}
