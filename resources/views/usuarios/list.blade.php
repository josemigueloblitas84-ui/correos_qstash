@extends('plantilla.app')

@section('title', 'Usuarios')

@section('content')

    <div class="app-content-header">
        <div class="container-fluid d-flex justify-content-between align-items-center">
            <h2>Usuarios</h2>

            @can('crear usuarios')
                <a href="{{ route('usuarios.create') }}" class="btn btn-primary">
                    Crear Usuario
                </a>
            @endcan
        </div>
    </div>

    <div class="app-content">
        <div class="container-fluid">

            @include('mensajes')

            <div class="card">
                <div class="card-body table-responsive">

                    <table id="example1" class="table table-bordered table-striped align-middle">
                        <thead class="table-light">
                            <tr>
                                <th width="80">#</th>
                                <th>Nombre</th>
                                <th>Correo</th>
                                <th>Departamento</th>
                                <th>Tipo de Personal</th>
                                <th>Rol</th>
                                <th width="320">Permisos Directos</th>
                                <th width="200">Creación</th>
                                <th width="240">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if ($usuarios->isNotEmpty())
                                @foreach ($usuarios as $usuario)
                                    @php
                                        $permisosAsignados = $usuario->permissions->pluck('name')->unique()->values();
                                    @endphp

                                    <tr>
                                        <td>{{ $usuario->id }}</td>
                                        <td>{{ $usuario->name }}</td>
                                        <td>{{ $usuario->email }}</td>
                                        <td>{{ $usuario->departamento_nombre ?? 'Sin departamento' }}</td>
                                        <td>{{ $usuario->tipo_personal_nombre ?? 'Sin tipo' }}</td>
                                        <td>
                                            <span class="badge text-bg-info">
                                                {{ $usuario->roles->pluck('name')->implode(', ') ?: 'Sin rol' }}
                                            </span>
                                        </td>
                                        <td style="white-space: normal; min-width: 220px;">
                                            @if ($permisosAsignados->isNotEmpty())
                                                @foreach ($permisosAsignados as $permiso)
                                                    <span class="badge text-bg-dark me-1 mb-1">{{ $permiso }}</span>
                                                @endforeach
                                            @else
                                                <span class="badge text-bg-light border">Sin permisos directos</span>
                                            @endif
                                        </td>
                                        <td>{{ $usuario->created_at->format('d / M / Y H:i:s') }}</td>
                                        <td>
                                            <div class="d-flex flex-wrap gap-2 align-items-center">
                                                @can('editar usuarios')
                                                    <a href="{{ route('usuarios.edit', encrypt_id($usuario->id)) }}"
                                                        class="btn btn-sm btn-primary">
                                                        Editar
                                                    </a>
                                                @endcan

                                                <div class="dropdown">
                                                    <button class="btn btn-sm btn-secondary dropdown-toggle"
                                                        type="button"
                                                        data-bs-toggle="dropdown"
                                                        aria-expanded="false">
                                                        Gestionar
                                                    </button>
                                                    <ul class="dropdown-menu dropdown-menu-end">
                                                        <li>
                                                            <a class="dropdown-item"
                                                                href="{{ route('usuarios.roles.edit', encrypt_id($usuario->id)) }}">
                                                                Roles
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <a class="dropdown-item"
                                                                href="{{ route('usuarios.personal.edit', encrypt_id($usuario->id)) }}">
                                                                Asignar personal
                                                            </a>
                                                        </li>

                                                        @can('asignar permiso especial')
                                                            <li>
                                                                <a class="dropdown-item"
                                                                    href="{{ route('usuarios.permisos.edit', encrypt_id($usuario->id)) }}">
                                                                    Permisos especiales
                                                                </a>
                                                            </li>
                                                        @endcan
                                                    </ul>
                                                </div>

                                                @can('eliminar usuarios')
                                                    <div class="form-check form-switch m-0 d-flex align-items-center gap-2">
                                                        <input
                                                            type="checkbox"
                                                            class="form-check-input js-toggle-estado-usuario"
                                                            data-id="{{ encrypt_id($usuario->id) }}"
                                                            {{ (int) $usuario->estado === 1 ? 'checked' : '' }}>
                                                        <label class="form-check-label small {{ (int) $usuario->estado === 1 ? 'text-success' : 'text-danger' }}">
                                                            {{ (int) $usuario->estado === 1 ? 'Activo' : 'Inactivo' }}
                                                        </label>
                                                    </div>
                                                @endcan
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="9" class="text-center py-4">
                                        No hay usuarios registrados.
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>

                </div>
            </div>

        </div>
    </div>

@endsection
@push('styles')
    <style>
        .js-toggle-estado-usuario {
            cursor: pointer;
        }

        .js-toggle-estado-usuario:not(:checked) {
            background-color: #dc3545;
            border-color: #dc3545;
        }

        .js-toggle-estado-usuario:checked {
            background-color: #198754;
            border-color: #198754;
        }

        .js-toggle-estado-usuario:focus {
            box-shadow: 0 0 0 0.2rem rgba(25, 135, 84, 0.25);
        }

        .js-toggle-estado-usuario:not(:checked):focus {
            box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
        }
    </style>
@endpush

@push('scripts')
    @if (session('role_assigned'))
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                Swal.fire({
                    icon: 'success',
                    title: @json(session('role_assigned')),
                    confirmButtonText: 'Aceptar',
                    timer: 2600,
                    timerProgressBar: true
                });
            });
        </script>
    @endif

    @if (session('user_created'))
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                Swal.fire({
                    icon: 'success',
                    title: 'Usuario creado',
                    text: @json(session('user_created')),
                    confirmButtonText: 'Aceptar',
                    timer: 2600,
                    timerProgressBar: true
                });
            });
        </script>
    @endif

    @if (session('user_updated'))
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                Swal.fire({
                    icon: 'success',
                    title: 'Usuario actualizado',
                    text: @json(session('user_updated')),
                    confirmButtonText: 'Aceptar',
                    timer: 2600,
                    timerProgressBar: true
                });
            });
        </script>
    @endif

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.js-toggle-estado-usuario').forEach(function (input) {
                input.addEventListener('change', function () {
                    const id = this.getAttribute('data-id');
                    const checked = this.checked;
                    const self = this;
                    const label = self.closest('.form-check')?.querySelector('.form-check-label');

                    if (!id) {
                        return;
                    }

                    $.ajax({
                        url: '{{ url('usuarios') }}/' + id + '/estado',
                        type: 'POST',
                        data: {
                            _method: 'PATCH',
                            _token: '{{ csrf_token() }}'
                        },
                        beforeSend: function () {
                            self.disabled = true;
                        },
                        success: function () {
                            if (label) {
                                label.textContent = checked ? 'Activo' : 'Inactivo';
                                label.classList.remove('text-success', 'text-danger');
                                label.classList.add(checked ? 'text-success' : 'text-danger');
                            }
                        },
                        error: function () {
                            self.checked = !checked;

                            if (label) {
                                label.textContent = self.checked ? 'Activo' : 'Inactivo';
                                label.classList.remove('text-success', 'text-danger');
                                label.classList.add(self.checked ? 'text-success' : 'text-danger');
                            }

                            alert('No se pudo actualizar el estado del usuario.');
                        },
                        complete: function () {
                            self.disabled = false;
                        }
                    });
                });
            });
        });
    </script>
@endpush
