@extends('plantilla.app')

@section('title', 'Usuarios / Crear')

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

                                <div class="mb-3">
                                    <label for="contrasenaUsuario" class="form-label fw-bold">Contraseña:</label>
                                    <input type="password" class="form-control"
                                        id="contrasenaUsuario" placeholder="Ingrese la contraseña del usuario"
                                        name="contrasenaUsuario">
                                    @error('contrasenaUsuario')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="confirmar_contrasenaUsuario" class="form-label fw-bold">Confirmar Contraseña:</label>
                                    <input type="password" class="form-control"
                                        id="confirmar_contrasenaUsuario" placeholder="Confirme la contraseña del usuario"
                                        name="confirmar_contrasenaUsuario">
                                    @error('confirmar_contrasenaUsuario')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
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
                                    'selectedInstitucionId' => old('institucion_id'),
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
                                            <input value="{{ old('celular') }}" type="text" inputmode="numeric" maxlength="12" pattern="[0-9]*" class="form-control"
                                                id="celular" name="celular" placeholder="Ingrese el celular">
                                            @error('celular')
                                                <small class="text-danger">{{ $message }}</small>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="telefono_contacto" class="form-label fw-bold">Teléfono de Contacto:</label>
                                            <input value="{{ old('telefono_contacto') }}" type="number" min="0" max="2147483647" step="1" class="form-control"
                                                id="telefono_contacto" name="telefono_contacto" placeholder="Ingrese el teléfono de contacto">
                                            @error('telefono_contacto')
                                                <small class="text-danger">{{ $message }}</small>
                                            @enderror
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
