@extends('plantilla.app')

@section('title', 'Permisos')

@section('content')

@include('mensajes')

<section class="content">
<div class="card">
    <div class="card-header d-flex justify-content-between">
        <h2>Permisos</h2>

        <a href="{{ route('permisos.create') }}" class="btn btn-primary ">
            Crear Permiso
        </a>
    </div>

    <div class="card-body p-0">
        <table class="table table-bordered table-striped">
            <thead>
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
                        <td>{{ $permiso->created_at->format('d / M / Y') }}</td>
                        <td>
                            <a href="{{ route('permisos.edit', $permiso->id) }}" class="btn btn-warning btn-sm">Editar</a>
                            <button onclick="eliminarPermiso({{ $permiso->id }})" class="btn btn-danger btn-sm">Eliminar</button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="card-footer">
        {{ $permisos->links() }}
    </div>
</div>
</section>

@endsection

@push('scripts')
<script>
function eliminarPermiso(id) {
    if (confirm('¿Desea eliminar el permiso?')) {
        $.ajax({
            url: '{{ route("permisos.destroy") }}',
            type: 'DELETE',
            data: {id:id},
            headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}"},
            success: function() {
                location.reload();
            }
        });
    }
}
</script>
@endpush