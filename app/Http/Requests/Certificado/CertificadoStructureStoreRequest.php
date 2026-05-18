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
            'elements.*.db_id' => ['nullable', 'integer', 'min:1'],
            'elements.*.type' => ['required', 'in:texto,campo_dinamico,firma,qr,imagen'],
            'elements.*.left' => ['required', 'numeric'],
            'elements.*.top' => ['required', 'numeric'],
            'elements.*.width' => ['required', 'numeric', 'min:1'],
            'elements.*.height' => ['required', 'numeric', 'min:1'],
            'elements.*.text' => ['nullable', 'string'],
            'elements.*.field' => ['nullable', 'in:nombre_completo,ci,horas_institucionales'],
            'elements.*.image_src' => ['nullable', 'string'],
            'elements.*.qr_value' => ['nullable', 'string'],
            'elements.*.qr_style' => ['nullable', 'array'],
            'elements.*.qr_style.foreground' => ['nullable', 'string'],
            'elements.*.qr_style.background' => ['nullable', 'string'],
            'elements.*.qr_style.eye' => ['nullable', 'string'],
            'elements.*.qr_style.pattern' => ['nullable', 'in:round,square'],
            'elements.*.qr_style.corner_frame_shape' => ['nullable', 'in:none,square,rounded,circle'],
            'elements.*.qr_style.corner_dot_shape' => ['nullable', 'in:none,square,circle'],
            'elements.*.qr_style.corner_top_left' => ['nullable', 'boolean'],
            'elements.*.qr_style.corner_top_right' => ['nullable', 'boolean'],
            'elements.*.qr_style.corner_bottom_left' => ['nullable', 'boolean'],
            'elements.*.qr_style.margin' => ['nullable', 'integer', 'min:0', 'max:10'],
            'elements.*.qr_style.scale' => ['nullable', 'integer', 'min:4', 'max:20'],
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
