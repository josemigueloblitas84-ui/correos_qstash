<?php

namespace App\Http\Requests\Agenda;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class InstitucionUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        if (! $this->ajax()) {
            throw new HttpResponseException(
                response()->json([
                    'message' => 'No puedes acceder a esta ruta.',
                ], 403)
            );
        }

        return true;
    }

    public function rules(): array
    {
        $id = $this->resolveTenantId();
        $domainId = DB::table('domains')
            ->where('tenant_id', $id)
            ->orderBy('id')
            ->value('id');

        return [
            'nombre' => [
                'required',
                'string',
                'min:3',
                'max:150',
                Rule::unique('tenants', 'nombre')->ignore($id, 'id'),
            ],
            'domain' => [
                'required',
                'string',
                'max:255',
                Rule::notIn(config('tenancy.central_domains')),
                Rule::unique('domains', 'domain')->ignore($domainId),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre de la institucion es obligatorio.',
            'nombre.string' => 'El nombre de la institucion debe ser texto.',
            'nombre.min' => 'El nombre de la institucion debe tener al menos :min caracteres.',
            'nombre.max' => 'El nombre de la institucion no puede superar :max caracteres.',
            'nombre.unique' => 'El nombre de la institucion ya existe.',
            'domain.required' => 'El dominio de la institucion es obligatorio.',
            'domain.string' => 'El dominio de la institucion debe ser texto.',
            'domain.max' => 'El dominio de la institucion no puede superar :max caracteres.',
            'domain.not_in' => 'El dominio seleccionado esta reservado para la aplicacion central.',
            'domain.unique' => 'El dominio de la institucion ya existe.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'domain' => $this->normalizeDomain($this->input('domain')),
        ]);
    }

    private function resolveTenantId(): ?string
    {
        $id = $this->route('id');

        if (! is_string($id) || $id === '') {
            return null;
        }

        return $id;
    }

    private function normalizeDomain(mixed $domain): string
    {
        $domain = strtolower(trim((string) $domain));
        $domain = preg_replace('#^https?://#', '', $domain) ?? $domain;

        return trim($domain, "/ \t\n\r\0\x0B");
    }
}
