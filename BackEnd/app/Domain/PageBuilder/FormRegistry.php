<?php

namespace App\Domain\PageBuilder;

use Illuminate\Validation\ValidationException;

final class FormRegistry
{
    /** @var array<string, FormDefinition> */
    private array $definitions;

    public function __construct()
    {
        $definitions = [$this->contact(), $this->quote(), $this->transport(), $this->warehouse()];
        $this->definitions = [];
        foreach ($definitions as $definition) {
            $this->definitions[$definition->blockType] = $definition;
        }
    }

    public function get(string $blockType, string $path = 'form'): FormDefinition
    {
        $definition = $this->definitions[$blockType] ?? null;
        if (! $definition instanceof FormDefinition) {
            throw ValidationException::withMessages([$path => [__('page_builder.validation.unknown_form')]]);
        }

        return $definition;
    }

    /** @return list<array<string, mixed>> */
    public function metadata(): array
    {
        return array_values(array_map(static fn (FormDefinition $definition): array => $definition->metadata(), $this->definitions));
    }

    private function contact(): FormDefinition
    {
        return new FormDefinition('form.contact', 'contact', 1, 'public.forms.contact', [
            $this->field('contact_name', 'contact_name', 'text', true, 'person_name', false, 'full', autocomplete: 'name'),
            $this->field('contact_phone', 'contact_phone', 'tel', false, 'phone', false, 'half', autocomplete: 'tel'),
            $this->field('contact_email', 'contact_email', 'email', false, 'email', false, 'half', autocomplete: 'email'),
            $this->field('company', 'company', 'text', false, 'short_text', false, 'half', autocomplete: 'organization'),
            $this->field('subject', 'subject', 'text', false, 'short_text', false, 'half'),
            $this->field('message', 'message', 'textarea', true, 'long_text', false, 'full'),
            $this->consent(),
        ]);
    }

    private function quote(): FormDefinition
    {
        return new FormDefinition('form.product-quote', 'product_quote', 1, 'public.forms.quote', [
            $this->field('quote_product', 'items[0][product_id]', 'hidden', true, 'published_product', false, 'hidden'),
            $this->field('quote_quantity', 'items[0][quantity]', 'number', false, 'positive_decimal', false, 'half'),
            $this->field('quote_unit', 'items[0][unit]', 'text', false, 'short_text', false, 'half'),
            $this->field('quote_notes', 'items[0][notes]', 'textarea', false, 'long_text', false, 'full'),
            $this->field('contact_name', 'contact_name', 'text', true, 'person_name', false, 'full', autocomplete: 'name'),
            $this->field('contact_phone', 'contact_phone', 'tel', true, 'phone', false, 'half', autocomplete: 'tel'),
            $this->field('contact_email', 'contact_email', 'email', false, 'email', false, 'half', autocomplete: 'email'),
            $this->field('message', 'message', 'textarea', false, 'long_text', false, 'full'),
            $this->consent(),
        ]);
    }

    private function transport(): FormDefinition
    {
        return new FormDefinition('form.transport-request', 'transport', 1, 'public.forms.transport', [
            $this->field('pickup_location', 'pickup_location', 'text', true, 'short_text', false, 'half'),
            $this->field('delivery_location', 'delivery_location', 'text', true, 'short_text', false, 'half'),
            $this->field('cargo_description', 'cargo_description', 'textarea', true, 'long_text', false, 'full'),
            $this->field('cargo_weight', 'cargo_weight', 'number', false, 'non_negative_decimal', false, 'half'),
            $this->field('weight_unit', 'weight_unit', 'select', false, 'weight_unit', false, 'half', options: ['kg', 'ton']),
            $this->field('vehicle_type', 'vehicle_type_id', 'select', false, 'published_vehicle_type', false, 'half', optionSource: 'vehicle_types'),
            $this->field('requested_date', 'requested_date', 'date', false, 'future_date', false, 'half'),
            $this->field('contact_name', 'contact_name', 'text', true, 'person_name', false, 'full', autocomplete: 'name'),
            $this->field('contact_phone', 'contact_phone', 'tel', true, 'phone', false, 'half', autocomplete: 'tel'),
            $this->field('contact_email', 'contact_email', 'email', false, 'email', false, 'half', autocomplete: 'email'),
            $this->consent(),
        ]);
    }

    private function warehouse(): FormDefinition
    {
        return new FormDefinition('form.warehouse-request', 'warehouse', 1, 'public.forms.warehouse', [
            $this->field('goods_description', 'goods_description', 'textarea', true, 'long_text', false, 'full'),
            $this->field('required_area', 'required_area', 'number', false, 'non_negative_decimal', false, 'half'),
            $this->field('area_unit', 'area_unit', 'select', false, 'area_unit', false, 'half', options: ['m2']),
            $this->field('required_volume', 'required_volume', 'number', false, 'non_negative_decimal', false, 'half'),
            $this->field('volume_unit', 'volume_unit', 'select', false, 'volume_unit', false, 'half', options: ['m3']),
            $this->field('duration_description', 'duration_description', 'text', false, 'short_text', false, 'half'),
            $this->field('start_date', 'start_date', 'date', false, 'future_date', false, 'half'),
            $this->field('storage_requirements', 'storage_requirements', 'textarea', false, 'long_text', false, 'full'),
            $this->field('preferred_location', 'preferred_location', 'text', false, 'short_text', false, 'half'),
            $this->field('warehouse', 'warehouse_id', 'select', false, 'published_warehouse', false, 'half', optionSource: 'warehouses'),
            $this->field('contact_name', 'contact_name', 'text', true, 'person_name', false, 'full', autocomplete: 'name'),
            $this->field('contact_phone', 'contact_phone', 'tel', true, 'phone', false, 'half', autocomplete: 'tel'),
            $this->field('contact_email', 'contact_email', 'email', false, 'email', false, 'half', autocomplete: 'email'),
            $this->consent(),
        ]);
    }

    private function consent(): FormFieldDefinition
    {
        return $this->field('consent', 'consent', 'checkbox', true, 'accepted', true, 'full');
    }

    /** @param list<string> $options */
    private function field(string $key, string $name, string $input, bool $required, string $validation, bool $consent, string $layout, ?string $optionSource = null, array $options = [], ?string $autocomplete = null): FormFieldDefinition
    {
        return new FormFieldDefinition($key, $name, $input, $required, $validation, $consent, $layout, $optionSource, $options, $autocomplete);
    }
}
