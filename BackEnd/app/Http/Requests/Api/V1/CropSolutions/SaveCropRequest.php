<?php

namespace App\Http\Requests\Api\V1\CropSolutions;

use App\Models\Crop;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class SaveCropRequest extends FormRequest
{
    /** @return array<string, mixed> */
    public function rules(): array
    {
        $crop = $this->route('crop');
        $cropId = $crop instanceof Crop ? $crop->getKey() : null;

        return [
            'category_id' => ['nullable', 'string', 'size:26', 'exists:hongvan_crop_categories,public_id'],
            'code' => ['required', 'string', 'max:64', Rule::unique('hongvan_crops', 'code')->ignore($cropId)],
            'image_media_id' => ['nullable', 'string', 'size:26', 'exists:hongvan_media,public_id'],
            'is_active' => ['required', 'boolean'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:65535'],
            'translations' => ['required', 'array', 'size:3'],
            'translations.*.locale' => ['required', 'distinct', Rule::in(['vi', 'en', 'zh'])],
            'translations.*.name' => ['required', 'string', 'max:255'],
            'translations.*.slug' => ['required', 'string', 'max:191', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'],
            'translations.*.summary' => ['nullable', 'string', 'max:2000'],
            'translations.*.description' => ['nullable', 'string', 'max:50000'],
            'translations.*.meta_title' => ['nullable', 'string', 'max:255'],
            'translations.*.meta_description' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
