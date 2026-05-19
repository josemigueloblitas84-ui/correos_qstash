@extends('plantilla.app')

@section('title', 'Usuarios / Crear')

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
            <div class="d-flex justify-content-between">
                <h1>Usuarios / Crear</h1>
            </div>
        </div>
    </div>

    <div class="app-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title">Crear Usuario</h3>
                        </div>

                        <form action="{{ route('usuarios.store') }}" method="POST" autocomplete="off">
                            @csrf
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="nombreUsuario" class="form-label fw-bold">Nombre:</label>
                                    <input value="{{ old('nombreUsuario') }}" type="text" class="form-control"
                                        id="nombreUsuario" placeholder="Ingrese el nombre del usuario" name="nombreUsuario">
                                    @error('nombreUsuario')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="correoUsuario" class="form-label fw-bold">Correo:</label>
                                    <input value="{{ old('correoUsuario') }}" type="email" class="form-control"
                                        id="correoUsuario" placeholder="Ingrese el correo del usuario" name="correoUsuario">
                                    @error('correoUsuario')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="contrasenaUsuario" class="form-label fw-bold">Contraseña:</label>
                                            <div class="password-input-wrap">
                                                <input type="password" class="form-control password-field @error('contrasenaUsuario') is-invalid @enderror"
                                                    id="contrasenaUsuario" placeholder="Ingrese la contraseña del usuario"
                                                    name="contrasenaUsuario">
                                                <button
                                                    type="button"
                                                    class="btn btn-link text-secondary js-toggle-password password-toggle-btn"
                                                    data-target="contrasenaUsuario"
                                                    aria-label="Mostrar u ocultar contraseña">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                            </div>
                                            <small class="text-muted d-block mt-1">
                                                Debe tener al menos 8 caracteres, una mayúscula, una minúscula, un número y un símbolo.
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
                                                <small class="text-danger">{{ $message }}</small>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="confirmar_contrasenaUsuario" class="form-label fw-bold">Confirmar Contraseña:</label>
                                            <div class="password-input-wrap">
                                                <input type="password" class="form-control password-field @error('confirmar_contrasenaUsuario') is-invalid @enderror"
                                                    id="confirmar_contrasenaUsuario" placeholder="Confirme la contraseña del usuario"
                                                    name="confirmar_contrasenaUsuario">
                                                <button
                                                    type="button"
                                                    class="btn btn-link text-secondary js-toggle-password password-toggle-btn"
                                                    data-target="confirmar_contrasenaUsuario"
                                                    aria-label="Mostrar u ocultar confirmación de contraseña">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                            </div>
                                            @error('confirmar_contrasenaUsuario')
                                                <small class="text-danger">{{ $message }}</small>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="departamento_id" class="form-label fw-bold">Departamento:</label>
                                            <select class="form-select" id="departamento_id" name="departamento_id">
                                                <option value="">Seleccione un departamento</option>
                                                @foreach ($departamentos as $departamento)
                                                    <option value="{{ $departamento->id }}" {{ old('departamento_id') == $departamento->id ? 'selected' : '' }}>
                                                        {{ $departamento->nombre_depa }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('departamento_id')
                                                <small class="text-danger">{{ $message }}</small>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="tipo_personal_id" class="form-label fw-bold">Tipo de Personal:</label>
                                            <select class="form-select" id="tipo_personal_id" name="tipo_personal_id">
                                                <option value="">Seleccione un tipo de personal</option>
                                                @foreach ($tiposPersonal as $tipoPersonal)
                                                    <option value="{{ $tipoPersonal->id }}" {{ old('tipo_personal_id') == $tipoPersonal->id ? 'selected' : '' }}>
                                                        {{ $tipoPersonal->tipo }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('tipo_personal_id')
                                                <small class="text-danger">{{ $message }}</small>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                @include('usuarios.partials.institucion-sede-fields', [
                                    'selectedSedeId' => old('sede_id'),
                                ])

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="cod_estudiante" class="form-label fw-bold">Codigo / documento de identidad:</label>
                                            <input value="{{ old('cod_estudiante') }}" type="text" class="form-control"
                                                id="cod_estudiante" name="cod_estudiante" placeholder="Ingrese el codigo o documento de identidad">
                                            @error('cod_estudiante')
                                                <small class="text-danger">{{ $message }}</small>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="cantidad_horas_totales" class="form-label fw-bold">Cantidad de Horas Totales:</label>
                                            <input value="{{ old('cantidad_horas_totales') }}" type="number" step="1" min="0" max="2147483647" class="form-control"
                                                id="cantidad_horas_totales" name="cantidad_horas_totales" placeholder="Ingrese la cantidad de horas">
                                            @error('cantidad_horas_totales')
                                                <small class="text-danger">{{ $message }}</small>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="celular" class="form-label fw-bold">Celular:</label>
                                            <div class="row g-2">
                                                <div class="col-md-5">
                                                    <select
                                                        id="celular_country"
                                                        name="celular_country"
                                                        class="form-select js-country-select @error('celular_country') is-invalid @enderror">
                                                        <option value="">Seleccione un pais</option>
                                                        @foreach ($countryOptions as $countryCode => $countryLabel)
                                                            <option value="{{ $countryCode }}" {{ old('celular_country') === $countryCode ? 'selected' : '' }}>
                                                                {{ $countryLabel }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    @error('celular_country')
                                                        <small class="text-danger">{{ $message }}</small>
                                                    @enderror
                                                </div>
                                                <div class="col-md-7">
                                                    <input value="{{ old('celular') }}" type="text" inputmode="numeric" pattern="[0-9]*" class="form-control @error('celular') is-invalid @enderror"
                                                        id="celular" name="celular" placeholder="Ej. 71234567">
                                                    @error('celular')
                                                        <small class="text-danger">{{ $message }}</small>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="telefono_contacto" class="form-label fw-bold">Teléfono de Contacto:</label>
                                            <div class="row g-2">
                                                <div class="col-md-5">
                                                    <select
                                                        id="telefono_contacto_country"
                                                        name="telefono_contacto_country"
                                                        class="form-select js-country-select @error('telefono_contacto_country') is-invalid @enderror">
                                                        <option value="">Seleccione un pais</option>
                                                        @foreach ($countryOptions as $countryCode => $countryLabel)
                                                            <option value="{{ $countryCode }}" {{ old('telefono_contacto_country') === $countryCode ? 'selected' : '' }}>
                                                                {{ $countryLabel }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    @error('telefono_contacto_country')
                                                        <small class="text-danger">{{ $message }}</small>
                                                    @enderror
                                                </div>
                                                <div class="col-md-7">
                                                    <input value="{{ old('telefono_contacto') }}" type="text" inputmode="numeric" pattern="[0-9]*" class="form-control @error('telefono_contacto') is-invalid @enderror"
                                                        id="telefono_contacto" name="telefono_contacto" placeholder="Ej. 22123456">
                                                    @error('telefono_contacto')
                                                        <small class="text-danger">{{ $message }}</small>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="card-footer">
                                <button type="submit" class="btn btn-primary my-2">Crear Usuario</button>
                            </div>
                        </form>
                    </div>
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
