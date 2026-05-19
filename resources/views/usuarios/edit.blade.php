@extends('plantilla.app')

@section('title', 'Editar Usuario')

@push('styles')
    <link rel="stylesheet" href="{{ global_asset('assets/css/passValidator.css') }}">
    <link rel="stylesheet" href="{{ global_asset('assets/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ global_asset('assets/plugins/flag-icon-css/css/flag-icons.min.css') }}">
@endpush

@php
    $phoneUtil = \libphonenumber\PhoneNumberUtil::getInstance();

    $countryOptions = collect($phoneUtil->getSupportedRegions())
        ->mapWithKeys(function (string $region) use ($phoneUtil) {
            $countryName = \Locale::getDisplayRegion('-' . $region, 'es') ?: $region;
            $dialCode = $phoneUtil->getCountryCodeForRegion($region);

            return [
                $region => $countryName . ' (+' . $dialCode . ')',
            ];
        })
        ->sort()
        ->all();
@endphp

@section('content')

    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Usuarios / Editar</h1>
                </div>
            </div>
        </div>
    </div>

    <div class="app-content">
        <div class="container-fluid">

            <div class="card card-primary">
                <div class="card-body">

                    <form action="{{ route('usuarios.update', $encryptedId) }}" method="POST" autocomplete="off">
                        @csrf

                        <div class="mb-3">
                            <label for="name" class="form-label">Nombre:</label>
                            <input
                                value="{{ old('name', $usuario->name) }}"
                                name="name"
                                id="name"
                                type="text"
                                placeholder="Ingrese el nombre"
                                class="form-control @error('name') is-invalid @enderror">

                            @error('name')
                                <div class="invalid-feedback d-block">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Correo:</label>
                            <input
                                value="{{ old('email', $usuario->email) }}"
                                name="email"
                                id="email"
                                type="text"
                                placeholder="Ingrese el correo"
                                class="form-control @error('email') is-invalid @enderror">

                            @error('email')
                                <div class="invalid-feedback d-block">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="contrasenaUsuario" class="form-label">Nueva contraseña:</label>
                                    <div class="password-input-wrap">
                                        <input
                                            name="contrasenaUsuario"
                                            id="contrasenaUsuario"
                                            type="password"
                                            placeholder="Deje en blanco para mantener la actual"
                                            class="form-control password-field @error('contrasenaUsuario') is-invalid @enderror">

                                        <button
                                            type="button"
                                            class="btn btn-link text-secondary js-toggle-password password-toggle-btn"
                                            data-target="contrasenaUsuario"
                                            aria-label="Mostrar u ocultar contraseña">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                    <small class="text-muted d-block mt-1">
                                        Si actualiza la contraseña, debe tener al menos 8 caracteres, una mayúscula, una minúscula, un número y un símbolo.
                                    </small>
                                    <div class="mt-2">
                                        <div class="password-strength-wrap">
                                            <div class="progress password-progress">
                                                <div id="passwordStrengthBar"
                                                    class="progress-bar bg-danger"
                                                    role="progressbar"
                                                    style="width: 0%;"
                                                    aria-valuemin="0"
                                                    aria-valuemax="100"
                                                    aria-valuenow="0">
                                                </div>
                                            </div>

                                            <div class="password-markers" id="passwordRules">
                                                <span id="rule-length" class="password-marker" title="Mínimo 8 caracteres">8</span>
                                                <span id="rule-upper" class="password-marker" title="Al menos 1 mayúscula">A</span>
                                                <span id="rule-lower" class="password-marker" title="Al menos 1 minúscula">a</span>
                                                <span id="rule-number" class="password-marker" title="Al menos 1 número">1</span>
                                                <span id="rule-special" class="password-marker" title="Al menos 1 carácter especial">*</span>
                                            </div>
                                        </div>

                                        <small id="passwordStrengthText" class="text-muted d-block mt-2">
                                            Seguridad de contraseña: 0%
                                        </small>
                                    </div>

                                    @error('contrasenaUsuario')
                                        <div class="invalid-feedback d-block">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="confirmar_contrasenaUsuario" class="form-label">Confirmar nueva contraseña:</label>
                                    <div class="password-input-wrap">
                                        <input
                                            name="confirmar_contrasenaUsuario"
                                            id="confirmar_contrasenaUsuario"
                                            type="password"
                                            placeholder="Repita la nueva contraseña"
                                            class="form-control password-field @error('confirmar_contrasenaUsuario') is-invalid @enderror">

                                        <button
                                            type="button"
                                            class="btn btn-link text-secondary js-toggle-password password-toggle-btn"
                                            data-target="confirmar_contrasenaUsuario"
                                            aria-label="Mostrar u ocultar confirmación de contraseña">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>

                                    @error('confirmar_contrasenaUsuario')
                                        <div class="invalid-feedback d-block">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="departamento_id" class="form-label">Departamento:</label>
                                    <select
                                        name="departamento_id"
                                        id="departamento_id"
                                        class="form-select @error('departamento_id') is-invalid @enderror">
                                        <option value="">Seleccione un departamento</option>
                                        @foreach ($departamentos as $departamento)
                                            <option value="{{ $departamento->id }}"
                                                {{ old('departamento_id', $usuario->departamento_id) == $departamento->id ? 'selected' : '' }}>
                                                {{ $departamento->nombre_depa }}
                                            </option>
                                        @endforeach
                                    </select>

                                    @error('departamento_id')
                                        <div class="invalid-feedback d-block">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="tipo_personal_id" class="form-label">Tipo de Personal:</label>
                                    <select
                                        name="tipo_personal_id"
                                        id="tipo_personal_id"
                                        class="form-select @error('tipo_personal_id') is-invalid @enderror">
                                        <option value="">Seleccione un tipo de personal</option>
                                        @foreach ($tiposPersonal as $tipoPersonal)
                                            <option value="{{ $tipoPersonal->id }}"
                                                {{ old('tipo_personal_id', $usuario->tipo_personal_id) == $tipoPersonal->id ? 'selected' : '' }}>
                                                {{ $tipoPersonal->tipo }}
                                            </option>
                                        @endforeach
                                    </select>

                                    @error('tipo_personal_id')
                                        <div class="invalid-feedback d-block">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        @include('usuarios.partials.institucion-sede-fields', [
                            'selectedSedeId' => old('sede_id', $usuario->sede_id),
                        ])

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="cod_estudiante" class="form-label">Codigo / documento de identidad:</label>
                                    <input
                                        value="{{ old('cod_estudiante', $usuario->cod_estudiante) }}"
                                        name="cod_estudiante"
                                        id="cod_estudiante"
                                        type="text"
                                        placeholder="Ingrese el codigo o documento de identidad"
                                        class="form-control @error('cod_estudiante') is-invalid @enderror">

                                    @error('cod_estudiante')
                                        <div class="invalid-feedback d-block">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="cantidad_horas_totales" class="form-label">Cantidad de Horas Totales:</label>
                                    <input
                                        value="{{ old('cantidad_horas_totales', $usuario->cantidad_horas_totales) }}"
                                        name="cantidad_horas_totales"
                                        id="cantidad_horas_totales"
                                        type="number"
                                        step="1"
                                        min="0"
                                        max="2147483647"
                                        placeholder="Ingrese la cantidad de horas"
                                        class="form-control @error('cantidad_horas_totales') is-invalid @enderror">

                                    @error('cantidad_horas_totales')
                                        <div class="invalid-feedback d-block">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="celular" class="form-label">Celular:</label>
                                    <div class="row g-2">
                                        <div class="col-md-5">
                                            <select
                                                name="celular_country"
                                                id="celular_country"
                                                class="form-select js-country-select @error('celular_country') is-invalid @enderror">
                                                <option value="">Seleccione un pais</option>
                                                @foreach ($countryOptions as $countryCode => $countryLabel)
                                                    <option value="{{ $countryCode }}"
                                                        {{ old('celular_country', $usuario->celular_country) === $countryCode ? 'selected' : '' }}>
                                                        {{ $countryLabel }}
                                                    </option>
                                                @endforeach
                                            </select>

                                            @error('celular_country')
                                                <div class="invalid-feedback d-block">
                                                    {{ $message }}
                                                </div>
                                            @enderror
                                        </div>

                                        <div class="col-md-7">
                                            <input
                                                value="{{ old('celular', $usuario->celular) }}"
                                                name="celular"
                                                id="celular"
                                                type="text"
                                                inputmode="numeric"
                                                pattern="[0-9]*"
                                                placeholder="Ej. 71234567"
                                                class="form-control @error('celular') is-invalid @enderror">

                                            @error('celular')
                                                <div class="invalid-feedback d-block">
                                                    {{ $message }}
                                                </div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="telefono_contacto" class="form-label">Teléfono de Contacto:</label>
                                    <div class="row g-2">
                                        <div class="col-md-5">
                                            <select
                                                name="telefono_contacto_country"
                                                id="telefono_contacto_country"
                                                class="form-select js-country-select @error('telefono_contacto_country') is-invalid @enderror">
                                                <option value="">Seleccione un pais</option>
                                                @foreach ($countryOptions as $countryCode => $countryLabel)
                                                    <option value="{{ $countryCode }}"
                                                        {{ old('telefono_contacto_country', $usuario->telefono_contacto_country) === $countryCode ? 'selected' : '' }}>
                                                        {{ $countryLabel }}
                                                    </option>
                                                @endforeach
                                            </select>

                                            @error('telefono_contacto_country')
                                                <div class="invalid-feedback d-block">
                                                    {{ $message }}
                                                </div>
                                            @enderror
                                        </div>

                                        <div class="col-md-7">
                                            <input
                                                value="{{ old('telefono_contacto', $usuario->telefono_contacto) }}"
                                                name="telefono_contacto"
                                                id="telefono_contacto"
                                                type="text"
                                                inputmode="numeric"
                                                pattern="[0-9]*"
                                                placeholder="Ej. 22123456"
                                                class="form-control @error('telefono_contacto') is-invalid @enderror">

                                            @error('telefono_contacto')
                                                <div class="invalid-feedback d-block">
                                                    {{ $message }}
                                                </div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-warning">
                            Actualizar
                        </button>

                    </form>

                </div>
            </div>

        </div>
    </div>

@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="{{ global_asset('assets/js/passValidator-unicode.js') }}"></script>
    <script src="{{ global_asset('assets/js/phone-country-select.js') }}"></script>
@endpush
