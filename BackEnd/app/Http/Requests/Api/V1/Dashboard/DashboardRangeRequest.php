<?php

namespace App\Http\Requests\Api\V1\Dashboard;

use Illuminate\Foundation\Http\FormRequest;

final class DashboardRangeRequest extends FormRequest
{
    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'from' => ['nullable', 'date_format:Y-m-d', 'before_or_equal:to'],
            'to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:from'],
            'timezone' => ['nullable', 'timezone:all'],
        ];
    }

    protected function passedValidation(): void
    {
        if ($this->filled('from') && $this->filled('to')) {
            $days = now()->parse((string) $this->input('from'))->diffInDays(now()->parse((string) $this->input('to')));
            abort_if($days > 366, 422, 'The dashboard date range may not exceed 366 days.');
        }
    }
}
