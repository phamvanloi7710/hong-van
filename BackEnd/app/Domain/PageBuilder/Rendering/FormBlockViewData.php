<?php

namespace App\Domain\PageBuilder\Rendering;

use App\Domain\Localization\LocaleRegistry;
use App\Domain\PageBuilder\FormContextSigner;
use App\Domain\PageBuilder\FormFieldDefinition;
use App\Domain\PageBuilder\FormOptionResolver;
use App\Domain\PageBuilder\FormRegistry;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Str;
use Illuminate\Support\ViewErrorBag;

final readonly class FormBlockViewData
{
    public function __construct(
        private FormRegistry $forms,
        private FormOptionResolver $options,
        private FormContextSigner $signer,
        private LocaleRegistry $locales,
        private LayoutClassResolver $classes,
    ) {}

    /**
     * @param  array<string, mixed>  $block
     * @return array<string, mixed>
     */
    public function make(array $block): array
    {
        $definition = $this->forms->get((string) ($block['type'] ?? ''));
        $context = is_array($block['_renderContext'] ?? null) ? $block['_renderContext'] : [];
        $locale = is_string($context['locale'] ?? null) ? $context['locale'] : app()->getLocale();
        $blockId = (string) ($block['id'] ?? '');
        $contextProductId = $definition->formType === 'product_quote'
            && ($context['type'] ?? null) === 'product'
            && is_string($context['publicId'] ?? null)
            ? $context['publicId'] : null;
        $contextMissing = $definition->formType === 'product_quote' && $contextProductId === null;
        $status = session('page_builder_form_status');
        $statusMatches = is_array($status)
            && ($status['block_id'] ?? null) === $blockId
            && ($status['form_type'] ?? null) === $definition->formType;
        $errors = session('errors');
        $props = is_array($block['props'] ?? null) ? $block['props'] : [];
        $localizedDefaults = [
            'title' => "page_builder_forms.forms.{$definition->formType}_title",
            'description' => "page_builder_forms.forms.{$definition->formType}_description",
            'submitLabel' => "page_builder_forms.forms.{$definition->formType}_submit",
            'successMessage' => 'page_builder_forms.ui.success',
        ];
        foreach ($localizedDefaults as $property => $translationKey) {
            if (! is_string($props[$property] ?? null) || trim($props[$property]) === '') {
                $props[$property] = (string) Lang::get($translationKey, [], $locale);
            }
        }

        $routeName = $locale === $this->locales->defaultLocale()
            ? $definition->endpointRoute
            : str_replace('public.forms.', 'public.forms.localized.', $definition->endpointRoute);

        return [
            'blockId' => $blockId,
            'classes' => $this->classes->classes($block),
            'props' => $props,
            'definition' => $definition,
            'action' => route($routeName, $locale === $this->locales->defaultLocale() ? [] : ['locale' => $locale]),
            'fields' => array_map(fn (FormFieldDefinition $field): array => $this->field($field, $locale, $blockId, $contextProductId), $definition->fields),
            'idempotencyKey' => (string) Str::uuid(),
            'contextToken' => $contextProductId === null ? null : $this->signer->sign($definition->formType, $blockId, 'product', $contextProductId),
            'contextMissing' => $contextMissing,
            'contextMissingLabel' => (string) Lang::get('page_builder_forms.ui.product_context_required', [], $locale),
            'success' => $statusMatches,
            'successMessage' => $statusMatches ? (string) ($status['message'] ?? $props['successMessage']) : '',
            'honeypotLabel' => (string) Lang::get('page_builder_forms.ui.website', [], $locale),
            'selectLabel' => (string) Lang::get('page_builder_forms.ui.select', [], $locale),
            'privacyLabel' => (string) Lang::get('page_builder_forms.ui.privacy', [], $locale),
            'privacyUrl' => route($locale === $this->locales->defaultLocale() ? 'public.privacy' : 'public.localized-privacy', $locale === $this->locales->defaultLocale() ? [] : ['locale' => $locale]),
            'errors' => $errors instanceof ViewErrorBag ? $errors : new ViewErrorBag,
        ];
    }

    /** @return array<string,mixed> */
    private function field(FormFieldDefinition $field, string $locale, string $blockId, ?string $contextProductId): array
    {
        $errorKey = preg_replace('/\[([^]]+)\]/', '.$1', $field->name) ?? $field->name;
        $fixedOptions = array_map(static fn (string $value): array => [
            'value' => $value,
            'label' => Lang::get('page_builder_forms.options.'.$value, [], $locale),
        ], $field->options);

        return [
            'id' => $blockId.'-'.$field->key,
            'key' => $field->key,
            'name' => $field->name,
            'errorKey' => ltrim($errorKey, '.'),
            'input' => $field->input,
            'label' => (string) Lang::get("page_builder_forms.fields.{$field->key}.label", [], $locale),
            'help' => (string) Lang::get("page_builder_forms.fields.{$field->key}.help", [], $locale),
            'required' => $field->required,
            'consent' => $field->consent,
            'layout' => $field->layout,
            'autocomplete' => $field->autocomplete,
            'options' => [...$fixedOptions, ...$this->options->resolve($field->optionSource, $locale)],
            'value' => $field->key === 'quote_product' ? $contextProductId : null,
        ];
    }
}
