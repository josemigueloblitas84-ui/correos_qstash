@extends('plantilla.app')

@section('title', 'Editar Rol')

@section('content')

    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Roles / Editar</h1>
                </div>
                {{--
                <div class="col-sm-6 text-end">
                    <a href="{{ route('roles.index') }}" class="btn btn-primary">
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

                    <form action="{{ route('roles.update', $role->id) }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label for="name" class="form-label fw-bold">Nombre:</label>
                            <input
                                value="{{ old('name', $role->name) }}"
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
                            <label class="form-label fw-bold">Permisos:</label>

                            <div class="row">
                                @if ($permisos->isNotEmpty())
                                    @foreach ($permisos as $permiso)
                                        <div class="col-md-3 mb-3">
                                            <div class="form-check form-switch">
                                                <input
                                                    {{ $hasPermisos->contains($permiso->name) ? 'checked' : '' }}
                                                    type="checkbox"
                                                    id="permiso-{{ $permiso->id }}"
                                                    name="permisos[]"
                                                    value="{{ $permiso->name }}"
                                                    class="form-check-input"
                                                    style="transform: scale(1.2);">
                                                <label
                                                    for="permiso-{{ $permiso->id }}"
                                                    class="form-check-label fw-bold">
                                                    {{ $permiso->name }}
                                                </label>
                                            </div>
                                        </div>
                                    @endforeach
                                @endif
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