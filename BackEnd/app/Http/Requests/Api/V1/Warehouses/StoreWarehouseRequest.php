<?php

namespace App\Http\Requests\Api\V1\Warehouses;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreWarehouseRequest extends FormRequest
{
    /** @return array<string,mixed> */
    public function rules(): array
    {
        return ['goods_description' => ['required', 'string', 'max:10000'], 'required_area' => ['nullable', 'numeric', 'min:0'], 'area_unit' => ['nullable', 'required_with:required_area', Rule::in(['m2'])], 'required_volume' => ['nullable', 'numeric', 'min:0'], 'volume_unit' => ['nullable', 'required_with:required_volume', Rule::in(['m3'])], 'duration_description' => ['nullable', 'string', 'max:255'], 'start_date' => ['nullable', 'date', 'after_or_equal:today'], 'storage_requirements' => ['nullable', 'string', 'max:10000'], 'preferred_location' => ['nullable', 'string', 'max:255'], 'warehouse_id' => ['nullable', 'string', 'size:26', Rule::exists('hongvan_warehouses', 'public_id')->where(fn ($query) => $query->where('status', 'published')->whereNull('deleted_at'))], 'contact_name' => ['required', 'string', 'max:255'], 'contact_phone' => ['required', 'string', 'max:32', 'regex:/^[0-9+().\s-]{7,32}$/'], 'contact_email' => ['nullable', 'email:rfc', 'max:255']];
    }
}
