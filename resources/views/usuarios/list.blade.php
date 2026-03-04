@extends('plantilla.app')

@section('title', 'Usuarios')

@section('content')

<div class="content">

    <section class="content-header">
        <div class="container-fluid d-flex justify-content-between align-items-center">
            <h1>Usuarios</h1>

            @can('nuevo permiso') 
                <a href="{{ route('roles.create') }}" class="btn btn-primary">
                    Crear Usuario #
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
                                <th width="200">Creación</th>
                                <th width="200">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if ($usuarios->isNotEmpty())
                                @foreach ($usuarios as $usuario)
                                    <tr>
                                        <td>{{ $usuario->id }}</td>
                                        <td>{{ $usuario->name }}</td>
                                        <td>{{ $usuario->email }}</td>
                                        <td>
                                            <span class="badge badge-info">
                                                {{ $usuario->roles->pluck('name')->implode(', ') ?: 'Sin rol' }}
                                            </span>
                                        </td>
                                        <td>{{ $usuario->created_at->format('d / M / Y H:i:s') }}</td>
                                        <td>
                                            @can('editar usuarios')
                                                <a href="{{ route('usuarios.edit', $usuario->id) }}" 
                                                   class="btn btn-sm btn-warning">
                                                    Editar
                                                </a>
                                            @endcan

                                            {{--@can('permisos especiales')--}}
                                                <a href="{{ route('usuarios.edit', $usuario->id) }}" 
                                                   class="btn btn-sm btn-info">
                                                    Permisos Especiales
                                                </a>
                                            {{--@endcan--}}
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


@section('scripts')
<script>
function eliminarRol(id) {
    if (confirm('¿Desea eliminar el rol?')) {
        $.ajax({
            url: '{{ route("roles.destroy") }}',
            type: 'DELETE',
            data: {id:id},
            dataType: 'json',
            headers: {
                'X-CSRF-TOKEN': "{{ csrf_token() }}"
            },
            success: function(response) {
                window.location.href = '{{ route("roles.index") }}';
            }
        });
    }
}
</script>
@endsection