<?php

namespace App\Http\Requests\Certificado;

use Illuminate\Foundation\Http\FormRequest;

class CertificadoPlantillaStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'min:3', 'max:150'],
            'archivo_pdf' => ['required', 'file', 'mimes:pdf', 'max:10240'],
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre de la plantilla es obligatorio.',
            'nombre.min' => 'El nombre de la plantilla debe tener al menos :min caracteres.',
            'nombre.max' => 'El nombre de la plantilla no puede superar :max caracteres.',
            'archivo_pdf.required' => 'Debes seleccionar un archivo PDF.',
            'archivo_pdf.mimes' => 'El archivo debe ser un PDF.',
            'archivo_pdf.max' => 'El PDF no puede superar los 10 MB.',
        ];
    }
}
