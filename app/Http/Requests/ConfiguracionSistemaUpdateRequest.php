<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ConfiguracionSistemaUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre_institucion' => ['required', 'string', 'min:3', 'max:150'],
            'logo_principal' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'logo_pdf' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'correo_institucional' => ['nullable', 'email', 'max:150'],
            'celular_institucional' => ['nullable', 'string', 'max:30'],
        ];
    }

    public function messages(): array
    {
        return [
            'nombre_institucion.required' => 'El nombre de la institucion es obligatorio.',
            'nombre_institucion.min' => 'El nombre de la institucion debe tener al menos :min caracteres.',
            'nombre_institucion.max' => 'El nombre de la institucion no puede superar :max caracteres.',
            'logo_principal.mimes' => 'El logo principal debe ser una imagen JPG, JPEG, PNG o WEBP.',
            'logo_principal.max' => 'El logo principal no puede superar los 2 MB.',
            'logo_pdf.mimes' => 'El logo para PDF debe ser una imagen JPG, JPEG, PNG o WEBP.',
            'logo_pdf.max' => 'El logo para PDF no puede superar los 2 MB.',
            'correo_institucional.email' => 'El correo institucional debe ser valido.',
            'correo_institucional.max' => 'El correo institucional no puede superar :max caracteres.',
            'celular_institucional.max' => 'El celular institucional no puede superar :max caracteres.',
        ];
    }
}
