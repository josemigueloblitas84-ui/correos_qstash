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

            <form
                id="js-form-asignar-rol"
                action="{{ route('usuarios.roles.update', $encryptedId) }}"
                method="POST"
                data-usuario="{{ $usuario->name }}"
                data-rol-actual="{{ $currentRole ?? '' }}">
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

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('js-form-asignar-rol');

            if (!form) {
                return;
            }

            let confirmed = false;

            form.addEventListener('submit', function (event) {
                if (confirmed) {
                    return;
                }

                event.preventDefault();

                const selectedRole = form.querySelector('input[name="role"]:checked');

                if (!selectedRole) {
                    return;
                }

                const userName = form.dataset.usuario || 'este usuario';
                const currentRole = form.dataset.rolActual || '';
                const nextRole = selectedRole.value;
                const message = currentRole && currentRole !== nextRole
                    ? `Se reemplazara el rol "${currentRole}" por "${nextRole}" para ${userName}.`
                    : `Se asignara el rol "${nextRole}" a ${userName}.`;

                Swal.fire({
                    icon: 'question',
                    title: 'Confirmar asignacion de rol',
                    text: message,
                    showCancelButton: true,
                    confirmButtonText: 'Si, guardar',
                    cancelButtonText: 'Cancelar',
                    reverseButtons: true,
                }).then((result) => {
                    if (!result.isConfirmed) {
                        return;
                    }

                    confirmed = true;
                    form.submit();
                });
            });
        });
    </script>
@endpush
