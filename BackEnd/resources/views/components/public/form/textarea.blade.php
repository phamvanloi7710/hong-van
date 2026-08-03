@props(['name', 'label', 'value' => null, 'help' => null, 'required' => false, 'rows' => 5])
@php
    $errorId = "{$name}-error";
    $helpId = "{$name}-help";
    $describedBy = $errors->has($name) ? $errorId : ($help ? $helpId : null);
@endphp

<div class="form-field">
    <label for="{{ $name }}">{{ $label }} @if ($required)<span aria-hidden="true">*</span>@endif</label>
    <textarea
        id="{{ $name }}"
        name="{{ $name }}"
        rows="{{ max(2, min(20, (int) $rows)) }}"
        @required($required)
        @if ($describedBy) aria-describedby="{{ $describedBy }}" @endif
        @if ($errors->has($name)) aria-invalid="true" @endif
        {{ $attributes }}
    >{{ old($name, $value) }}</textarea>
    @if ($help && ! $errors->has($name))<p id="{{ $helpId }}" class="form-field__help">{{ $help }}</p>@endif
    @error($name)<p id="{{ $errorId }}" class="form-field__error">{{ $message }}</p>@enderror
</div>
