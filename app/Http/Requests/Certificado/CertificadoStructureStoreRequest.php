<?php

namespace App\Http\Requests\Certificado;

use Illuminate\Foundation\Http\FormRequest;

class CertificadoStructureStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'page' => ['required', 'array'],
            'page.size' => ['required', 'in:a4,carta'],
            'page.orientation' => ['required', 'in:horizontal,vertical'],
            'page.canvas_width' => ['required', 'numeric', 'min:100'],
            'page.canvas_height' => ['required', 'numeric', 'min:100'],

            'elements' => ['required', 'array'],
            'elements.*.type' => ['required', 'in:texto,campo_dinamico,firma,qr,imagen'],
            'elements.*.left' => ['required', 'numeric'],
            'elements.*.top' => ['required', 'numeric'],
            'elements.*.width' => ['required', 'numeric', 'min:1'],
            'elements.*.height' => ['required', 'numeric', 'min:1'],
            'elements.*.text' => ['nullable', 'string'],
            'elements.*.field' => ['nullable', 'in:nombre_completo,ci,horas_institucionales'],
            'elements.*.image_src' => ['nullable', 'string'],
            'elements.*.style' => ['nullable', 'array'],
            'elements.*.style.fontSize' => ['nullable', 'numeric'],
            'elements.*.style.fontFamily' => ['nullable', 'string'],
            'elements.*.style.fill' => ['nullable', 'string'],
            'elements.*.style.fontWeight' => ['nullable', 'string'],
            'elements.*.style.fontStyle' => ['nullable', 'string'],
            'elements.*.style.underline' => ['nullable', 'boolean'],
            'elements.*.style.textAlign' => ['nullable', 'in:left,center,right'],
            'elements.*.style.lineHeight' => ['nullable', 'numeric'],
            'elements.*.style.charSpacing' => ['nullable', 'numeric'],
        ];
    }
}
