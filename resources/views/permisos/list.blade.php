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
                                <a href="{{ route('permisos.edit', $permiso->id) }}" class="btn btn-warning btn-sm">
                                    Editar
                                </a>
                                @endcan

                                @can('eliminar permisos')
                                <button onclick="eliminarPermiso({{ $permiso->id }})" class="btn btn-danger btn-sm">
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
<script>
    function eliminarPermiso(id) {
        if (confirm('¿Desea eliminar el permiso?')) {
            $.ajax({
                url: '{{ route('permisos.destroy') }}',
                type: 'DELETE',
                data: { id: id },
                headers: {
                    'X-CSRF-TOKEN': "{{ csrf_token() }}"
                },
                success: function () {
                    location.reload();
                }
            });
        }
    }
</script>
@endpush