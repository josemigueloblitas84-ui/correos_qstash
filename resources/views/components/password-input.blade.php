@props([
    'name',
    'id' => null,
    'placeholder' => 'Password',
    'value' => null
])

@php
    $inputId = $id ?? $name;
@endphp

<div class="input-group mb-3">

    <input 
        type="password"
        name="{{ $name }}"
        id="{{ $inputId }}"
        value="{{ old($name, $value) }}"
        placeholder="{{ $placeholder }}"
        {{ $attributes->merge([
            'class' => 'form-control password-field ' . ($errors->has($name) ? 'is-invalid' : '')
        ]) }}
    >

    <div class="input-group-append">
        <span class="input-group-text toggle-password" style="cursor:pointer;">
            <i class="fas fa-eye"></i>
        </span>
    </div>

</div>

@error($name)
    <span class="invalid-feedback d-block">
        {{ $message }}
    </span>
@enderror