<?php

namespace App\Http\Requests\Api\V1\Warehouses;

use App\Models\Warehouse;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

final class SaveWarehouseRequest extends FormRequest
{
    /** @return array<string,mixed> */
    public function rules(): array
    {
        $model = $this->route('warehouse');
        $id = $model instanceof Warehouse ? $model->getKey() : null;

        return ['code' => ['required', 'string', 'max:100', Rule::unique('hongvan_warehouses', 'code')->ignore($id)], 'area_value' => ['nullable', 'numeric', 'min:0'], 'area_unit' => ['nullable', Rule::in(['m2'])], 'latitude' => ['nullable', 'numeric', 'between:-90,90', 'required_with:longitude'], 'longitude' => ['nullable', 'numeric', 'between:-180,180', 'required_with:latitude'], 'map_display' => ['required', Rule::in(['hidden', 'approximate', 'exact'])], 'business_hours' => ['nullable', 'array', 'max:7'], 'business_hours.*.day' => ['required', 'distinct', Rule::in(['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'])], 'business_hours.*.opens' => ['nullable', 'date_format:H:i'], 'business_hours.*.closes' => ['nullable', 'date_format:H:i'], 'business_hours.*.closed' => ['required', 'boolean'], 'status' => ['required', Rule::in(['draft', 'published', 'scheduled', 'archived'])], 'is_featured' => ['required', 'boolean'], 'sort_order' => ['required', 'integer', 'min:0', 'max:65535'], 'published_at' => ['nullable', 'date'], 'unpublished_at' => ['nullable', 'date', 'after:published_at'], 'facility_ids' => ['nullable', 'array', 'max:50'], 'facility_ids.*' => ['required', 'distinct', 'string', 'size:26', 'exists:hongvan_warehouse_facilities,public_id'], 'service_ids' => ['nullable', 'array', 'max:50'], 'service_ids.*' => ['required', 'distinct', 'string', 'size:26', 'exists:hongvan_warehouse_services,public_id'], 'translations' => ['required', 'array', 'size:3'], 'translations.*.locale' => ['required', 'distinct', Rule::in(['vi', 'en', 'zh'])], 'translations.*.name' => ['required', 'string', 'max:255'], 'translations.*.slug' => ['required', 'string', 'max:191', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'], 'translations.*.summary' => ['nullable', 'string', 'max:2000'], 'translations.*.description' => ['nullable', 'string', 'max:50000'], 'translations.*.address_display' => ['nullable', 'string', 'max:2000'], 'translations.*.area_description' => ['nullable', 'string', 'max:2000'], 'translations.*.capacity_description' => ['nullable', 'string', 'max:2000'], 'translations.*.security_description' => ['nullable', 'string', 'max:5000'], 'translations.*.fire_safety_description' => ['nullable', 'string', 'max:5000'], 'translations.*.business_hours_description' => ['nullable', 'string', 'max:2000'], 'translations.*.meta_title' => ['nullable', 'string', 'max:255'], 'translations.*.meta_description' => ['nullable', 'string', 'max:2000'], 'media' => ['nullable', 'array', 'max:30'], 'media.*.media_id' => ['required', 'distinct', 'string', 'size:26', 'exists:hongvan_media,public_id'], 'media.*.role' => ['required', Rule::in(['hero', 'gallery', 'floorplan'])], 'media.*.sort_order' => ['required', 'integer', 'min:0', 'max:65535']];
    }

    /** @return array<int,callable(Validator):void> */
    public function after(): array
    {
        return [function (Validator $validator): void {
            if ($this->input('status') === 'scheduled' && blank($this->input('published_at'))) {
                $validator->errors()->add('published_at', __('warehouses.scheduled_requires_date'));
            }
            if ($this->input('map_display') !== 'hidden' && (blank($this->input('latitude')) || blank($this->input('longitude')))) {
                $validator->errors()->add('map_display', __('warehouses.map_requires_coordinates'));
            }
        }];
    }
}
