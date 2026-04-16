@extends('plantilla.app')

@section('title', 'Editar Usuario')

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

                    <form action="{{ route('usuarios.update', $usuario->id) }}" method="POST">
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

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="cod_estudiante" class="form-label">Código de Estudiante:</label>
                                    <input
                                        value="{{ old('cod_estudiante', $usuario->cod_estudiante) }}"
                                        name="cod_estudiante"
                                        id="cod_estudiante"
                                        type="text"
                                        placeholder="Ingrese el código de estudiante"
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
                                        step="0.01"
                                        min="0"
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
                                    <input
                                        value="{{ old('celular', $usuario->celular) }}"
                                        name="celular"
                                        id="celular"
                                        type="number"
                                        placeholder="Ingrese el celular"
                                        class="form-control @error('celular') is-invalid @enderror">

                                    @error('celular')
                                        <div class="invalid-feedback d-block">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="telefono_contacto" class="form-label">Teléfono de Contacto:</label>
                                    <input
                                        value="{{ old('telefono_contacto', $usuario->telefono_contacto) }}"
                                        name="telefono_contacto"
                                        id="telefono_contacto"
                                        type="number"
                                        placeholder="Ingrese el teléfono de contacto"
                                        class="form-control @error('telefono_contacto') is-invalid @enderror">

                                    @error('telefono_contacto')
                                        <div class="invalid-feedback d-block">
                                            {{ $message }}
                                        </div>
                                    @enderror
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
