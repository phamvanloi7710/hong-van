<?php

namespace App\Http\Requests\Api\V1\Analytics;

use App\Domain\Analytics\AnalyticsConfiguration;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateConsentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $version = app(AnalyticsConfiguration::class)->get()['policy_version'];

        return [
            'analytics' => ['required', 'boolean'],
            'marketing' => ['required', 'boolean'],
            'policy_version' => ['required', 'string', Rule::in([$version])],
        ];
    }
}
