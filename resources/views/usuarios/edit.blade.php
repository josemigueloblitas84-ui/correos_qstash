@extends('plantilla.app')

@section('title', 'Editar Usuario')

@section('content')

<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Usuarios / Editar</h1>
            </div>
            {{--<div class="col-sm-6 text-right">
                <a href="{{ route('usuarios.index') }}" class="btn btn-primary">
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

                <form action="{{ route('usuarios.update', $usuario->id) }}" method="POST">
                    @csrf

                    <div class="form-group">
                        <label for="name">Nombre:</label>
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

                    <div class="form-group">
                        <label for="email">Correo:</label>
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

                    <div class="form-group">
                        <label>Roles:</label>

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