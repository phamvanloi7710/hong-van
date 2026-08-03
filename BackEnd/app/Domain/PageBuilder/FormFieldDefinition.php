<?php

namespace App\Domain\PageBuilder;

use Illuminate\Support\Facades\Lang;

final readonly class FormFieldDefinition
{
    /** @param list<string> $options */
    public function __construct(
        public string $key,
        public string $name,
        public string $input,
        public bool $required,
        public string $validationPreset,
        public bool $consent,
        public string $layout,
        public ?string $optionSource = null,
        public array $options = [],
        public ?string $autocomplete = null,
    ) {}

    /** @return array<string, mixed> */
    public function metadata(): array
    {
        return [
            'key' => $this->key,
            'name' => $this->name,
            'input' => $this->input,
            'labels' => $this->translations('label'),
            'help' => $this->translations('help'),
            'required' => $this->required,
            'validationPreset' => $this->validationPreset,
            'consent' => $this->consent,
            'layout' => $this->layout,
            'options' => $this->options,
            'optionSource' => $this->optionSource,
            'autocomplete' => $this->autocomplete,
        ];
    }

    /** @return array{vi: string, en: string, zh: string} */
    private function translations(string $part): array
    {
        $key = "page_builder_forms.fields.{$this->key}.{$part}";

        return [
            'vi' => (string) Lang::get($key, [], 'vi'),
            'en' => (string) Lang::get($key, [], 'en'),
            'zh' => (string) Lang::get($key, [], 'zh'),
        ];
    }
}
