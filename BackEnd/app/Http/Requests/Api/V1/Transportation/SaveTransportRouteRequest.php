<?php

namespace App\Http\Requests\Api\V1\Transportation;

use App\Models\TransportRoute;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class SaveTransportRouteRequest extends FormRequest
{
    /** @return array<string,mixed> */
    public function rules(): array
    {
        $model = $this->route('route');
        $id = $model instanceof TransportRoute ? $model->getKey() : null;

        return ['code' => ['required', 'string', 'max:100', Rule::unique('hongvan_transport_routes', 'code')->ignore($id)], 'origin_code' => ['required', 'string', 'max:100'], 'destination_code' => ['required', 'string', 'max:100'], 'status' => ['required', Rule::in(['draft', 'published', 'scheduled', 'archived'])], 'is_featured' => ['required', 'boolean'], 'sort_order' => ['required', 'integer', 'min:0', 'max:65535'], 'published_at' => ['nullable', 'date'], 'unpublished_at' => ['nullable', 'date', 'after:published_at'], 'translations' => ['required', 'array', 'size:3'], 'translations.*.locale' => ['required', 'distinct', Rule::in(['vi', 'en', 'zh'])], 'translations.*.name' => ['required', 'string', 'max:255'], 'translations.*.slug' => ['required', 'string', 'max:191', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'], 'translations.*.summary' => ['nullable', 'string', 'max:2000']];
    }
}
