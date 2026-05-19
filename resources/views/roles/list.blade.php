@extends('plantilla.app')

@section('title', 'Roles')

@section('content')

    <div class="app-content-header">
        <div class="container-fluid d-flex justify-content-between align-items-center">
            <h1>Roles</h1>

            @can('crear roles')
                <a href="{{ route('roles.create') }}" class="btn btn-primary">
                    Crear Rol
                </a>
            @endcan
        </div>
    </div>

    <div class="app-content">
        <div class="container-fluid">

            @include('mensajes')

            <div class="card">
                <div class="card-body table-responsive">

                    <table id="rolesTable" class="table table-hover align-middle">
                        <thead class="table-light">
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
                                            <span class="badge text-bg-info">
                                                {{ $role->name }}
                                            </span>
                                        </td>

                                        <td style="white-space: normal; min-width: 220px;">
                                            @forelse ($role->permissions as $permission)
                                                <span class="badge text-bg-secondary me-1 mb-1">
                                                    {{ $permission->name }}
                                                </span>
                                            @empty
                                                <span class="badge text-bg-light">Sin permisos</span>
                                            @endforelse
                                        </td>

                                        <td>{{ $role->created_at->format('d / M / Y: H:i:s') }}</td>

                                        <td>
                                            @can('editar roles')
                                                <a href="{{ route('roles.edit', encrypt_id($role->id)) }}"
                                                    class="btn btn-sm btn-warning">
                                                    Editar
                                                </a>
                                            @endcan

                                            @can('eliminar roles')
                                                <a href="javascript:void(0)" onclick="eliminarRol('{{ encrypt_id($role->id) }}')"
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
                {{ $roles->links('pagination::bootstrap-5') }}
            </div>

        </div>
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
                url: "{{ global_asset('assets/datatables/i18n/es-ES.json') }}"
            },
            columnDefs: [
                { responsivePriority: 1, targets: 2 },
                { responsivePriority: 2, targets: 1 },
                { responsivePriority: 3, targets: 0 },
                { responsivePriority: 100, targets: 3 },
                { responsivePriority: 101, targets: 4 }
            ]
        });
    });

    @if (session('role_created'))
    document.addEventListener('DOMContentLoaded', function () {
        Swal.fire({
            icon: 'success',
            title: 'Rol creado',
            text: @json(session('role_created')),
            confirmButtonText: 'Aceptar',
            timer: 2600,
            timerProgressBar: true
        });
    });
    @endif

    @if (session('role_updated'))
    document.addEventListener('DOMContentLoaded', function () {
        Swal.fire({
            icon: 'success',
            title: 'Rol actualizado',
            text: @json(session('role_updated')),
            confirmButtonText: 'Aceptar',
            timer: 2600,
            timerProgressBar: true
        });
    });
    @endif

    function eliminarRol(id) {
        Swal.fire({
            icon: 'warning',
            title: '¿Desea eliminar el rol?',
            text: 'Esta accion no se puede deshacer.',
            showCancelButton: true,
            confirmButtonText: 'Si, eliminar',
            cancelButtonText: 'Cancelar',
            reverseButtons: true
        }).then((result) => {
            if (!result.isConfirmed) {
                return;
            }

            $.ajax({
                url: '{{ route('roles.destroy') }}',
                type: 'DELETE',
                data: { id: id },
                dataType: 'json',
                headers: {
                    'X-CSRF-TOKEN': "{{ csrf_token() }}"
                },
                success: function(response) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Rol eliminado',
                        text: response.message,
                        confirmButtonText: 'Aceptar',
                        timer: 2200,
                        timerProgressBar: true
                    }).then(() => {
                        window.location.href = '{{ route('roles.index') }}';
                    });
                },
                error: function(xhr) {
                    const response = xhr.responseJSON || {};

                    Swal.fire({
                        icon: 'error',
                        title: 'No se pudo eliminar',
                        text: response.message || 'Ocurrio un error al eliminar el rol.',
                        confirmButtonText: 'Aceptar'
                    });
                }
            });
        });
    }
</script>
@endpush
