@props(['name', 'label', 'type' => 'text', 'value' => null, 'help' => null, 'required' => false])
@php
    $inputType = in_array($type, ['text', 'email', 'tel', 'url', 'number', 'date'], true) ? $type : 'text';
    $errorId = "{$name}-error";
    $helpId = "{$name}-help";
    $describedBy = $errors->has($name) ? $errorId : ($help ? $helpId : null);
@endphp

<div class="form-field">
    <label for="{{ $name }}">{{ $label }} @if ($required)<span aria-hidden="true">*</span>@endif</label>
    <input
        id="{{ $name }}"
        name="{{ $name }}"
        type="{{ $inputType }}"
        value="{{ old($name, $value) }}"
        @required($required)
        @if ($describedBy) aria-describedby="{{ $describedBy }}" @endif
        @if ($errors->has($name)) aria-invalid="true" @endif
        {{ $attributes }}
    >
    @if ($help && ! $errors->has($name))<p id="{{ $helpId }}" class="form-field__help">{{ $help }}</p>@endif
    @error($name)<p id="{{ $errorId }}" class="form-field__error">{{ $message }}</p>@enderror
</div>
