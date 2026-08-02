<?php

namespace App\Http\Requests\Api\V1\Settings;

use App\Models\SettingGroup;
use Illuminate\Foundation\Http\FormRequest;

final class UpdateSettingGroupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $group = $this->route('settingGroup');
        $groupKey = $group instanceof SettingGroup ? $group->key : '';
        $definitions = config('company_settings.groups.'.$groupKey.'.settings', []);
        $keys = is_array($definitions) ? array_keys($definitions) : [];
        $rules = [
            'values' => ['required', 'array', 'min:1'],
            'values.*' => ['nullable'],
        ];

        foreach ($keys as $key) {
            $definition = $definitions[$key] ?? [];
            $configuredRules = is_array($definition) && is_array($definition['rules'] ?? null)
                ? $definition['rules']
                : ['nullable', 'string'];
            $rules['values.'.$key] = ['sometimes', ...$configuredRules];
        }

        $rules['values'] = [...$rules['values'], function (string $attribute, mixed $value, \Closure $fail) use ($keys): void {
            if (is_array($value) && array_diff(array_keys($value), $keys) !== []) {
                $fail('The '.$attribute.' field contains an unknown setting key.');
            }
        }];

        return $rules;
    }
}
