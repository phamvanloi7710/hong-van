<?php

namespace App\Http\Requests\Api\V1\Transportation;

use App\Models\Vehicle;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

final class SaveVehicleRequest extends FormRequest
{
    /** @return array<string,mixed> */
    public function rules(): array
    {
        $model = $this->route('vehicle');
        $id = $model instanceof Vehicle ? $model->getKey() : null;

        return ['vehicle_type_id' => ['required', 'string', 'size:26', 'exists:hongvan_vehicle_types,public_id'], 'code' => ['required', 'string', 'max:100', Rule::unique('hongvan_vehicles', 'code')->ignore($id)], 'payload_capacity' => ['nullable', 'numeric', 'min:0'], 'payload_unit' => ['nullable', Rule::in(['kg', 'ton'])], 'availability_display' => ['required', Rule::in(['available', 'limited', 'unavailable', 'contact'])], 'status' => ['required', Rule::in(['draft', 'published', 'scheduled', 'archived'])], 'is_featured' => ['required', 'boolean'], 'sort_order' => ['required', 'integer', 'min:0', 'max:65535'], 'published_at' => ['nullable', 'date'], 'unpublished_at' => ['nullable', 'date', 'after:published_at'], 'translations' => ['required', 'array', 'size:3'], 'translations.*.locale' => ['required', 'distinct', Rule::in(['vi', 'en', 'zh'])], 'translations.*.name' => ['required', 'string', 'max:255'], 'translations.*.slug' => ['required', 'string', 'max:191', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'], 'translations.*.summary' => ['nullable', 'string', 'max:2000'], 'translations.*.description' => ['nullable', 'string', 'max:50000'], 'translations.*.body_dimensions' => ['nullable', 'string', 'max:2000'], 'translations.*.meta_title' => ['nullable', 'string', 'max:255'], 'translations.*.meta_description' => ['nullable', 'string', 'max:2000'], 'media' => ['nullable', 'array', 'max:30'], 'media.*.media_id' => ['required', 'distinct', 'string', 'size:26', 'exists:hongvan_media,public_id'], 'media.*.role' => ['required', Rule::in(['hero', 'gallery'])], 'media.*.sort_order' => ['required', 'integer', 'min:0', 'max:65535']];
    }

    /** @return array<int,callable(Validator):void> */
    public function after(): array
    {
        return [function (Validator $v): void {
            if ($this->input('status') === 'scheduled' && blank($this->input('published_at'))) {
                $v->errors()->add('published_at', __('transportation.scheduled_requires_date'));
            }
        }];
    }
}
