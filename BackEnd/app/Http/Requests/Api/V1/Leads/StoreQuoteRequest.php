<?php

namespace App\Http\Requests\Api\V1\Leads;

use App\Domain\PageBuilder\FormContextSigner;
use App\Http\Requests\Concerns\PageBuilderFormContract;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

final class StoreQuoteRequest extends FormRequest
{
    use PageBuilderFormContract;

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            ...$this->pageBuilderContractRules('product_quote@1'),
            'contact_name' => ['required', 'string', 'max:255'],
            'contact_phone' => ['required', 'string', 'max:32', 'regex:/^[0-9+().\s-]{7,32}$/'],
            'contact_email' => ['nullable', 'email:rfc', 'max:255'],
            'message' => ['nullable', 'string', 'max:10000'],
            'items' => ['required', 'array', 'min:1', 'max:30'],
            'items.*.product_id' => ['required', 'string', 'size:26', Rule::exists('hongvan_products', 'public_id')->where(fn ($query) => $query->where('status', 'published')->whereNotNull('published_at')->where('published_at', '<=', now('UTC'))->where(fn ($visible) => $visible->whereNull('unpublished_at')->orWhere('unpublished_at', '>', now('UTC')))->whereNull('deleted_at'))],
            'items.*.quantity' => ['nullable', 'numeric', 'gt:0'],
            'items.*.unit' => ['nullable', 'string', 'max:32'],
            'items.*.notes' => ['nullable', 'string', 'max:2000'],
            'consent' => ['required', 'accepted'],
            'privacy_policy_version' => ['required', Rule::in([(string) config('leads.privacy_policy_version')])],
            'website' => ['nullable', 'string', 'max:0'],
            'form_context_token' => [$this->pageBuilderFormPresence(), 'string', 'max:4096'],
        ];
    }

    /** @return list<callable(Validator): void> */
    public function after(): array
    {
        return [function (Validator $validator): void {
            $token = $this->input('form_context_token');
            if (! is_string($token) || $token === '') {
                return;
            }
            $context = app(FormContextSigner::class)->verify($token, 'product_quote');
            $items = $this->input('items');
            $productId = is_array($items) ? data_get($items, '0.product_id') : null;
            $blockId = $this->input('_block_id');
            if ($context === null) {
                $validator->errors()->add('form_context_token', __('page_builder_forms.validation.invalid_context'));

                return;
            }
            if (! is_array($items) || ! is_string($productId) || $productId !== $context['context_public_id'] || count($items) !== 1) {
                $validator->errors()->add('items.0.product_id', __('page_builder_forms.validation.context_mismatch'));
            }
            if (is_string($blockId) && $blockId !== $context['block']) {
                $validator->errors()->add('_block_id', __('page_builder_forms.validation.context_mismatch'));
            }
        }];
    }
}
