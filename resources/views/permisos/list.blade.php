@extends('plantilla.app')

@section('title', 'Permisos')

@section('content')

<div class="app-content-header">
    <div class="container-fluid d-flex justify-content-between align-items-center">
        <h2>Permisos</h2>

        @can('crear permisos')
        <a href="{{ route('permisos.create') }}" class="btn btn-primary">
            Crear Permiso
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
                            <th width="200">Creación</th>
                            <th width="200">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($permisos as $permiso)
                        <tr>
                            <td>{{ $permiso->id }}</td>
                            <td>{{ $permiso->name }}</td>
                            <td>{{ $permiso->created_at->format('d / M / Y H:i:s') }}</td>
                            <td>
                                @can('editar permisos')
                                <a href="{{ route('permisos.edit', encrypt_id($permiso->id)) }}" class="btn btn-warning btn-sm">
                                    Editar
                                </a>
                                @endcan

                                @can('eliminar permisos')
                                <button onclick="eliminarPermiso('{{ encrypt_id($permiso->id) }}')" class="btn btn-danger btn-sm">
                                    Eliminar
                                </button>
                                @endcan
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>

            </div>
        </div>

    </div>
</div>

@endsection

@push('scripts')
@if (session('permission_created'))
<script>
    document.addEventListener('DOMContentLoaded', function () {
        Swal.fire({
            icon: 'success',
            title: 'Permiso creado',
            text: @json(session('permission_created')),
            confirmButtonText: 'Aceptar',
            timer: 2600,
            timerProgressBar: true
        });
    });
</script>
@endif

@if (session('permission_updated'))
<script>
    document.addEventListener('DOMContentLoaded', function () {
        Swal.fire({
            icon: 'success',
            title: 'Permiso actualizado',
            text: @json(session('permission_updated')),
            confirmButtonText: 'Aceptar',
            timer: 2600,
            timerProgressBar: true
        });
    });
</script>
@endif

<script>
    function eliminarPermiso(id) {
        Swal.fire({
            icon: 'warning',
            title: '¿Desea eliminar el permiso?',
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
                url: '{{ route('permisos.destroy') }}',
                type: 'DELETE',
                data: { id: id },
                headers: {
                    'X-CSRF-TOKEN': "{{ csrf_token() }}"
                },
                success: function (response) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Permiso eliminado',
                        text: response.message,
                        confirmButtonText: 'Aceptar',
                        timer: 2200,
                        timerProgressBar: true
                    }).then(() => {
                        location.reload();
                    });
                },
                error: function (xhr) {
                    const response = xhr.responseJSON || {};

                    Swal.fire({
                        icon: 'error',
                        title: 'No se pudo eliminar',
                        text: response.message || 'Ocurrio un error al eliminar el permiso.',
                        confirmButtonText: 'Aceptar'
                    });
                }
            });
        });
    }
</script>
@endpush
