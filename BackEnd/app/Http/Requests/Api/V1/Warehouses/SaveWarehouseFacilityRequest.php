<?php

namespace App\Http\Requests\Api\V1\Warehouses;

use App\Models\WarehouseFacility;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class SaveWarehouseFacilityRequest extends FormRequest
{
    /** @return array<string,mixed> */
    public function rules(): array
    {
        $model = $this->route('facility');
        $id = $model instanceof WarehouseFacility ? $model->getKey() : null;

        return ['code' => ['required', 'string', 'max:64', Rule::unique('hongvan_warehouse_facilities', 'code')->ignore($id)], 'icon' => ['nullable', 'string', 'max:64', 'regex:/^[a-z0-9_]+$/'], 'is_active' => ['required', 'boolean'], 'sort_order' => ['required', 'integer', 'min:0', 'max:65535'], 'translations' => ['required', 'array', 'size:3'], 'translations.*.locale' => ['required', 'distinct', Rule::in(['vi', 'en', 'zh'])], 'translations.*.name' => ['required', 'string', 'max:255'], 'translations.*.description' => ['nullable', 'string', 'max:2000']];
    }
}
