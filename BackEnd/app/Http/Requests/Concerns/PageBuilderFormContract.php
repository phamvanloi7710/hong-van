<?php

namespace App\Http\Requests\Concerns;

use Illuminate\Validation\Rule;

trait PageBuilderFormContract
{
    /** @return array<string, mixed> */
    private function pageBuilderContractRules(string $contract): array
    {
        $presence = $this->routeIs('public.forms.*', 'public.forms.localized.*') ? 'required' : 'nullable';

        return [
            '_form_definition' => [$presence, 'string', Rule::in([$contract])],
            '_block_id' => [$presence, 'string', 'regex:/^[A-Za-z0-9_-]{8,64}$/'],
            '_idempotency_key' => [$presence, 'uuid'],
            'form_context_token' => ['nullable', 'string', 'max:4096'],
        ];
    }

    private function pageBuilderFormPresence(): string
    {
        return $this->routeIs('public.forms.*', 'public.forms.localized.*') ? 'required' : 'nullable';
    }
}
