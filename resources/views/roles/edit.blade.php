@extends('plantilla.app')

@section('title', 'Editar Rol')

@section('content')

<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Roles / Editar</h1>
            </div>
            {{--<div class="col-sm-6 text-right">
                <a href="{{ route('roles.index') }}" class="btn btn-primary">
                    Volver
                </a>
            </div>--}}
        </div>
    </div>
</section>

<section class="content">
    <div class="container-fluid">

        <div class="card card-primary">
            <div class="card-body">

                <form action="{{ route('roles.update', $role->id) }}" method="POST">
                    @csrf

                    <div class="form-group">
                        <label for="name">Nombre:</label>
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

                    <div class="form-group">
                        <label>Permisos:</label>

                        <div class="row">
                            @if ($permisos->isNotEmpty())
                                @foreach ($permisos as $permiso)
                                    <div class="col-md-3">
                                        <div class="form-check">
                                            <input 
                                                {{ $hasPermisos->contains($permiso->name) ? 'checked' : '' }}
                                                type="checkbox"
                                                id="permiso-{{ $permiso->id }}"
                                                name="permisos[]"
                                                value="{{ $permiso->name }}"
                                                class="form-check-input">
                                            <label 
                                                for="permiso-{{ $permiso->id }}"
                                                class="form-check-label">
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
</section>

@endsection