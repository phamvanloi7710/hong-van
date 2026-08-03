<?php

namespace App\Domain\PageBuilder;

use Illuminate\Support\Facades\Lang;

final readonly class FormDefinition
{
    /** @param list<FormFieldDefinition> $fields */
    public function __construct(
        public string $blockType,
        public string $formType,
        public int $version,
        public string $endpointRoute,
        public array $fields,
    ) {}

    public function contract(): string
    {
        return $this->formType.'@'.$this->version;
    }

    /** @return array<string, mixed> */
    public function metadata(): array
    {
        $labelKey = "page_builder_forms.forms.{$this->formType}_label";

        return [
            'blockType' => $this->blockType,
            'formType' => $this->formType,
            'version' => $this->version,
            'labels' => [
                'vi' => (string) Lang::get($labelKey, [], 'vi'),
                'en' => (string) Lang::get($labelKey, [], 'en'),
                'zh' => (string) Lang::get($labelKey, [], 'zh'),
            ],
            'fields' => array_map(static fn (FormFieldDefinition $field): array => $field->metadata(), $this->fields),
        ];
    }
}
