@extends('plantilla.app')

@section('title', 'Usuarios')

@section('content')

    <div class="content">

        <section class="content-header">
            <div class="container-fluid d-flex justify-content-between align-items-center">
                <h1>Usuarios</h1>

                @can('crear usuarios')
                    <a href="{{ route('usuarios.create') }}" class="btn btn-primary">
                        Crear Usuario
                    </a>
                @endcan
            </div>
        </section>

        <section class="content">
            <div class="container-fluid">

                @include('mensajes')

                <div class="card">
                    <div class="card-body table-responsive p-0">

                        <table id="example1" class="table table-hover text-nowrap">
                            <thead class="thead-light">
                                <tr>
                                    <th width="80">#</th>
                                    <th>Nombre</th>
                                    <th>Correo</th>
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
                                            <td>
                                                <span class="badge badge-info">
                                                    {{ $usuario->roles->pluck('name')->implode(', ') ?: 'Sin rol' }}
                                                </span>
                                            </td>
                                            <td>
                                                @if ($permisosAsignados->isNotEmpty())
                                                    <div class="d-flex flex-wrap" style="gap: .25rem; white-space: normal;">
                                                        @foreach ($permisosAsignados as $permiso)
                                                            <span class="badge badge-dark">{{ $permiso }}</span>
                                                        @endforeach
                                                    </div>
                                                @else
                                                    <span class="badge badge-light">Sin permisos Directos</span>
                                                @endif
                                            </td>
                                            <td>{{ $usuario->created_at->format('d / M / Y H:i:s') }}</td>
                                            <td>
                                                @can('editar usuarios')
                                                    <a href="{{ route('usuarios.edit', $usuario->id) }}"
                                                        class="btn btn-sm btn-warning">
                                                        Editar
                                                    </a>
                                                @endcan

                                                @can('eliminar usuarios')
                                                    <a href="javascript:void(0)" onclick="eliminarUsuario({{ $usuario->id }})"
                                                        class="btn btn-sm btn-danger">
                                                        Eliminar
                                                    </a>
                                                @endcan

                                                @can('asignar permiso especial')
                                                    <a href="{{ route('usuarios.permisos.edit', $usuario->id) }}"
                                                        class="btn btn-sm btn-info">
                                                        Permisos Especiales
                                                    </a>
                                                @endcan
                                            </td>
                                        </tr>
                                    @endforeach
                                @endif
                            </tbody>
                        </table>

                    </div>
                </div>

                <div class="mt-3">
                    {{ $usuarios->links('pagination::bootstrap-4') }}
                </div>

            </div>
        </section>

    </div>

@endsection


@push('scripts')
    <script>
        function eliminarUsuario(id) {
            if (confirm('¿Desea eliminar el usuario?')) {
                $.ajax({
                    url: '{{ route('usuarios.destroy') }}',
                    type: 'DELETE',
                    data: {
                        id: id
                    },
                    dataType: 'json',
                    headers: {
                        'X-CSRF-TOKEN': "{{ csrf_token() }}"
                    },
                    success: function(response) {
                        window.location.href = '{{ route('usuarios.index') }}';
                    }
                });
            }
        }
    </script>
@endpush
