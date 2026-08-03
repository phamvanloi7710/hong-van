<?php

namespace App\Http\Requests\Api\V1\Seo;

use Illuminate\Foundation\Http\FormRequest;

final class SaveRobotsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'disallow_paths' => ['nullable', 'string', 'max:4000', function (string $attribute, mixed $value, \Closure $fail): void {
                foreach (preg_split('/\R/', (string) $value) ?: [] as $line) {
                    $line = trim($line);
                    if ($line !== '' && (! str_starts_with($line, '/') || str_contains($line, '#'))) {
                        $fail(__('seo.robots_path'));

                        return;
                    }
                }
            }],
        ];
    }
}
