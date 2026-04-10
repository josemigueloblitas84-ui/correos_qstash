@extends('plantilla.app')

@section('title', 'Editar Usuario')

@section('content')

    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Usuarios / Editar</h1>
                </div>
                {{--
                <div class="col-sm-6 text-end">
                    <a href="{{ route('usuarios.index') }}" class="btn btn-primary">
                        Volver
                    </a>
                </div>
                --}}
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

                        {{-- <div class="mb-3">
                            <label class="form-label">Roles:</label>

                            <div class="row">
                                @if ($roles->isNotEmpty())
                                    @foreach ($roles as $role)
                                        <div class="col-md-3">
                                            <div class="form-check">
                                                <input
                                                    {{ $hasRoles->contains($role->id) ? 'checked' : '' }}
                                                    type="checkbox"
                                                    id="role-{{ $role->id }}"
                                                    name="role[]"
                                                    value="{{ $role->name }}"
                                                    class="form-check-input">
                                                <label
                                                    for="role-{{ $role->id }}"
                                                    class="form-check-label">
                                                    {{ $role->name }}
                                                </label>
                                            </div>
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                        </div> --}}

                        <button type="submit" class="btn btn-warning">
                            Actualizar
                        </button>

                    </form>

                </div>
            </div>

        </div>
    </div>

@endsection
