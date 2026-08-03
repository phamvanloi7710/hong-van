<?php

namespace App\Http\Requests\Api\V1\Transportation;

use App\Models\VehicleType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class SaveVehicleTypeRequest extends FormRequest
{
    /** @return array<string,mixed> */
    public function rules(): array
    {
        $model = $this->route('type');
        $id = $model instanceof VehicleType ? $model->getKey() : null;

        return ['code' => ['required', 'string', 'max:64', Rule::unique('hongvan_vehicle_types', 'code')->ignore($id)], 'is_active' => ['required', 'boolean'], 'sort_order' => ['required', 'integer', 'min:0', 'max:65535'], 'translations' => ['required', 'array', 'size:3'], 'translations.*.locale' => ['required', 'distinct', Rule::in(['vi', 'en', 'zh'])], 'translations.*.name' => ['required', 'string', 'max:255'], 'translations.*.description' => ['nullable', 'string', 'max:2000']];
    }
}
