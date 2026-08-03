<?php

namespace App\Http\Requests\Api\V1\Transportation;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreTransportRequest extends FormRequest
{
    /** @return array<string,mixed> */
    public function rules(): array
    {
        return ['pickup_location' => ['required', 'string', 'max:255'], 'delivery_location' => ['required', 'string', 'max:255'], 'cargo_description' => ['required', 'string', 'max:5000'], 'cargo_weight' => ['nullable', 'numeric', 'min:0'], 'weight_unit' => ['nullable', Rule::in(['kg', 'ton'])], 'vehicle_type_id' => ['nullable', 'string', 'size:26', 'exists:hongvan_vehicle_types,public_id'], 'requested_date' => ['nullable', 'date', 'after_or_equal:today'], 'contact_name' => ['required', 'string', 'max:255'], 'contact_phone' => ['required', 'string', 'max:32'], 'contact_email' => ['nullable', 'email:rfc', 'max:255'], 'consent' => ['required', 'accepted'], 'privacy_policy_version' => ['required', Rule::in([(string) config('leads.privacy_policy_version')])], 'website' => ['nullable', 'string', 'max:0']];
    }
}
