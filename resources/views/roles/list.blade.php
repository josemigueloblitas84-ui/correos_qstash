@extends('plantilla.app')

@section('title', 'Roles')

@section('content')

    <div class="content">

        <section class="content-header">
            <div class="container-fluid d-flex justify-content-between align-items-center">
                <h1>Roles</h1>

                @can('crear roles')
                    <a href="{{ route('roles.create') }}" class="btn btn-primary">
                        Crear Rol
                    </a>
                @endcan
            </div>
        </section>

        <section class="content">
            <div class="container-fluid">

                @include('mensajes')

                <div class="card">
                    <div class="card-body table-responsive p-0">

                        <table id="rolesTable" class="table table-hover">
                            <thead class="thead-light">
                                <tr>
                                    <th>#</th>
                                    <th>Nombre</th>
                                    <th>Permisos</th>
                                    <th width="150">Creación</th>
                                    <th width="150">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if ($roles->isNotEmpty())
                                    @foreach ($roles as $role)
                                        <tr>
                                            <td>{{ $role->id }}</td>
                                            <td>
                                                <span class="badge badge-info">
                                                    {{ $role->name }}
                                                </span>
                                            </td>
                                            <td style="white-space: normal; min-width: 220px;">
                                                @forelse ($role->permissions as $permission)
                                                    <span class="badge badge-secondary mr-1 mb-1">
                                                        {{ $permission->name }}
                                                    </span>
                                                @empty
                                                    <span class="badge badge-light">Sin permisos</span>
                                                @endforelse
                                            </td>
                                            <td>{{ $role->created_at->format('d / M / Y') }}</td>
                                            <td>
                                                @can('editar roles')
                                                    <a href="{{ route('roles.edit', $role->id) }}"
                                                        class="btn btn-sm btn-warning">
                                                        Editar
                                                    </a>
                                                @endcan

                                                @can('eliminar roles')
                                                    <a href="javascript:void(0)" onclick="eliminarRol({{ $role->id }})"
                                                        class="btn btn-sm btn-danger">
                                                        Eliminar
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
                    {{ $roles->links('pagination::bootstrap-4') }}
                </div>

            </div>
        </section>

    </div>

@endsection


@push('scripts')
    <script>
        $(function() {
            $('#rolesTable').DataTable({
                dom: 'Bfrtip',
                responsive: true,
                lengthChange: false,
                autoWidth: false,
                buttons: ['copy', 'csv', 'excel', 'pdf', 'print', 'colvis'],
                language: {
                    url: "{{ asset('assets/datatables/i18n/es-ES.json') }}"
                },
                columnDefs: [{
                        responsivePriority: 1,
                        targets: 2
                    }, // Permisos
                    {
                        responsivePriority: 2,
                        targets: 1
                    }, // Nombre
                    {
                        responsivePriority: 3,
                        targets: 0
                    }, // #
                    {
                        responsivePriority: 100,
                        targets: 3
                    }, // Creación
                    {
                        responsivePriority: 101,
                        targets: 4
                    } // Acciones
                ]
            });
        });

        function eliminarRol(id) {
            if (confirm('¿Desea eliminar el rol?')) {
                $.ajax({
                    url: '{{ route('roles.destroy') }}',
                    type: 'DELETE',
                    data: {
                        id: id
                    },
                    dataType: 'json',
                    headers: {
                        'X-CSRF-TOKEN': "{{ csrf_token() }}"
                    },
                    success: function() {
                        window.location.href = '{{ route('roles.index') }}';
                    }
                });
            }
        }
    </script>
@endpush
