<?php

namespace App\Http\Requests\Api\V1\Settings;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

final class ReplaceBusinessHoursRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'hours' => ['required', 'array', 'max:7'],
            'hours.*' => ['required', 'array:day_of_week,opens_at,closes_at,is_closed,note,is_active'],
            'hours.*.day_of_week' => ['required', 'integer', 'between:0,6', 'distinct'],
            'hours.*.opens_at' => ['nullable', 'date_format:H:i'],
            'hours.*.closes_at' => ['nullable', 'date_format:H:i'],
            'hours.*.is_closed' => ['required', 'boolean'],
            'hours.*.note' => ['nullable', 'string', 'max:255'],
            'hours.*.is_active' => ['required', 'boolean'],
        ];
    }

    protected function passedValidation(): void
    {
        foreach ($this->validated('hours', []) as $index => $hour) {
            if (! $hour['is_closed'] && ($hour['opens_at'] === null || $hour['closes_at'] === null)) {
                throw ValidationException::withMessages([
                    'hours.'.$index.'.opens_at' => ['Opening and closing times are required for an open day.'],
                ]);
            }
        }
    }
}
