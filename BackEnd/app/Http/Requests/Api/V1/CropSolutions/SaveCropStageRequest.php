<?php

namespace App\Http\Requests\Api\V1\CropSolutions;

use App\Models\Crop;
use App\Models\CropStage;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class SaveCropStageRequest extends FormRequest
{
    /** @return array<string, mixed> */
    public function rules(): array
    {
        $stage = $this->route('stage');
        $stageId = $stage instanceof CropStage ? $stage->getKey() : null;
        $cropId = Crop::query()->where('public_id', $this->input('crop_id'))->value('id');

        return [
            'crop_id' => ['required', 'string', 'size:26', 'exists:hongvan_crops,public_id'],
            'code' => [
                'required', 'string', 'max:64',
                Rule::unique('hongvan_crop_stages', 'code')
                    ->where(fn ($query) => $query->where('crop_id', $cropId))
                    ->ignore($stageId),
            ],
            'image_media_id' => ['nullable', 'string', 'size:26', 'exists:hongvan_media,public_id'],
            'is_active' => ['required', 'boolean'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:65535'],
            'translations' => ['required', 'array', 'size:3'],
            'translations.*.locale' => ['required', 'distinct', Rule::in(['vi', 'en', 'zh'])],
            'translations.*.name' => ['required', 'string', 'max:255'],
            'translations.*.summary' => ['nullable', 'string', 'max:2000'],
            'translations.*.content' => ['nullable', 'string', 'max:50000'],
        ];
    }
}
