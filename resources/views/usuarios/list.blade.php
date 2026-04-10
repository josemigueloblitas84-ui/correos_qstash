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
                                <th>Rol</th>
                                <th width="320">Permisos Directos</th>
                                <th width="200">Creación</th>
                                <th width="200">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if ($usuarios->isNotEmpty())
                                @foreach ($usuarios as $usuario)
                                    @php
                                        $permisosAsignados = $usuario->permissions
                                            ->pluck('name')
                                            ->unique()
                                            ->values();
                                    @endphp

                                    <tr>
                                        <td>{{ $usuario->id }}</td>
                                        <td>{{ $usuario->name }}</td>
                                        <td>{{ $usuario->email }}</td>
                                        <td>{{ $usuario->departamento_nombre ?? 'Sin departamento' }}</td>
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
                                            @can('editar usuarios')
                                                <a href="{{ route('usuarios.edit', $usuario->id) }}"
                                                    class="btn btn-warning btn-sm">
                                                    Editar
                                                </a>
                                            @endcan

                                            @can('eliminar usuarios')
                                                <button onclick="eliminarUsuario({{ $usuario->id }})"
                                                    class="btn btn-danger btn-sm">
                                                    Eliminar
                                                </button>
                                            @endcan

                                            @can('asignar permiso especial')
                                                <a href="{{ route('usuarios.permisos.edit', $usuario->id) }}"
                                                    class="btn btn-info btn-sm text-white">
                                                    Permisos Especiales
                                                </a>
                                            @endcan
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="7" class="text-center py-4">
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

@push('scripts')
<script>
    function eliminarUsuario(id) {
        if (confirm('¿Desea eliminar el usuario?')) {
            $.ajax({
                url: '{{ route('usuarios.destroy') }}',
                type: 'DELETE',
                data: { id: id },
                dataType: 'json',
                headers: {
                    'X-CSRF-TOKEN': "{{ csrf_token() }}"
                },
                success: function() {
                    location.reload();
                }
            });
        }
    }
</script>
@endpush