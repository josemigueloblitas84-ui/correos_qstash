@extends('plantilla.app')

@section('title', 'Asignar Rol')

@section('content')
<div class="app-content-header">
    <div class="container-fluid">
        <h1>Usuarios / Asignar Rol</h1>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">Asignar Rol a {{ $usuario->name }}</h3>
            </div>

            <form action="{{ route('usuarios.roles.update', $encryptedId) }}" method="POST">
                @csrf

                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold d-block">Seleccione un rol</label>

                        @foreach ($roles as $role)
                            <div class="form-check mb-2">
                                <input
                                    class="form-check-input"
                                    type="radio"
                                    name="role"
                                    id="role-{{ $role->id }}"
                                    value="{{ $role->name }}"
                                    {{ old('role', $currentRole) == $role->name ? 'checked' : '' }}>
                                <label class="form-check-label" for="role-{{ $role->id }}">
                                    {{ $role->name }}
                                </label>
                            </div>
                        @endforeach

                        @error('role')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                </div>

                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">Guardar Rol</button>
                    <a href="{{ route('usuarios.index') }}" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
