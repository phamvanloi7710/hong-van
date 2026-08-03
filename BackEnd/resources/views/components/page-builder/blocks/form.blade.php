<section class="{{ implode(' ', $classes) }} pb-form-block" data-block-id="{{ $blockId }}">
    @if (($props['title'] ?? '') !== '')<h2>{{ $props['title'] }}</h2>@endif
    @if (($props['description'] ?? '') !== '')<p class="pb-form-block__description">{{ $props['description'] }}</p>@endif

    @if ($contextMissing)
        <div class="pb-form-block__notice" role="status">{{ $contextMissingLabel }}</div>
    @else
        <form class="pb-form" method="POST" action="{{ $action }}" novalidate>
            @csrf
            <input type="hidden" name="_form_definition" value="{{ $definition->contract() }}">
            <input type="hidden" name="_block_id" value="{{ $blockId }}">
            <input type="hidden" name="_idempotency_key" value="{{ $idempotencyKey }}">
            <input type="hidden" name="privacy_policy_version" value="{{ config('leads.privacy_policy_version') }}">
            @if ($contextToken !== null)<input type="hidden" name="form_context_token" value="{{ $contextToken }}">@endif

            <div class="pb-form__grid">
                @foreach ($fields as $field)
                    @if ($field['input'] === 'hidden')
                        <input type="hidden" name="{{ $field['name'] }}" value="{{ $field['value'] }}">
                    @else
                        <div class="pb-form__field pb-form__field--{{ $field['layout'] }} @error($field['errorKey']) pb-form__field--invalid @enderror">
                            @if ($field['input'] === 'checkbox')
                                <label class="pb-form__checkbox" for="{{ $field['id'] }}">
                                    <input id="{{ $field['id'] }}" name="{{ $field['name'] }}" type="checkbox" value="1" @checked(old($field['errorKey'])) required aria-describedby="{{ $field['id'] }}-help {{ $field['id'] }}-error">
                                    <span>{{ $field['label'] }} <a href="{{ $privacyUrl }}">{{ $privacyLabel }}</a></span>
                                </label>
                            @else
                                <label for="{{ $field['id'] }}">{{ $field['label'] }}@if($field['required']) <span aria-hidden="true">*</span>@endif</label>
                                @if ($field['input'] === 'textarea')
                                    <textarea id="{{ $field['id'] }}" name="{{ $field['name'] }}" rows="5" @if($field['required']) required aria-required="true" @endif aria-describedby="{{ $field['id'] }}-help {{ $field['id'] }}-error">{{ old($field['errorKey']) }}</textarea>
                                @elseif ($field['input'] === 'select')
                                    <select id="{{ $field['id'] }}" name="{{ $field['name'] }}" @if($field['required']) required aria-required="true" @endif aria-describedby="{{ $field['id'] }}-help {{ $field['id'] }}-error">
                                        <option value="">{{ $selectLabel }}</option>
                                        @foreach ($field['options'] as $option)<option value="{{ $option['value'] }}" @selected(old($field['errorKey']) === $option['value'])>{{ $option['label'] }}</option>@endforeach
                                    </select>
                                @else
                                    <input id="{{ $field['id'] }}" name="{{ $field['name'] }}" type="{{ $field['input'] }}" value="{{ old($field['errorKey']) }}" @if($field['input'] === 'number') min="0" step="any" @endif @if($field['autocomplete']) autocomplete="{{ $field['autocomplete'] }}" @endif @if($field['required']) required aria-required="true" @endif aria-describedby="{{ $field['id'] }}-help {{ $field['id'] }}-error">
                                @endif
                            @endif
                            <small id="{{ $field['id'] }}-help" class="pb-form__help">{{ $field['help'] }}</small>
                            @error($field['errorKey'])<p id="{{ $field['id'] }}-error" class="pb-form__error" role="alert">{{ $message }}</p>@else<span id="{{ $field['id'] }}-error"></span>@enderror
                        </div>
                    @endif
                @endforeach
            </div>

            <div class="pb-form__honeypot" aria-hidden="true">
                <label for="{{ $blockId }}-website">{{ $honeypotLabel }}</label>
                <input id="{{ $blockId }}-website" name="website" type="text" value="" tabindex="-1" autocomplete="off">
            </div>
            <button class="pb-form__submit" type="submit">{{ $props['submitLabel'] }}</button>
            <div class="pb-form__status" role="status" aria-live="polite">@if($success){{ $successMessage }}@endif</div>
        </form>
    @endif
</section>
