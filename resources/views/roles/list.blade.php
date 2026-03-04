@extends('plantilla.app')

@section('title', 'Roles')

@section('content')

<div class="content">

    <section class="content-header">
        <div class="container-fluid d-flex justify-content-between align-items-center">
            <h1>Roles</h1>

            {{-- @can('crear roles') --}}
                <a href="{{ route('roles.create') }}" class="btn btn-primary">
                    Crear Rol
                </a>
            {{-- @endcsan --}}
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
                                <th>Permisos</th>
                                <th width="200">Creación</th>
                                <th width="200">Acciones</th>
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
                                        <td>
                                            {{ $role->permissions->pluck('name')->join(', ') }}
                                        </td>
                                        <td>{{ $role->created_at->format('d / M / Y') }}</td>
                                        <td>
                                            {{-- @can('editar roles') --}}
                                                <a href="{{ route('roles.edit', $role->id) }}"
                                                   class="btn btn-sm btn-warning">
                                                    Editar
                                                </a>
                                            {{-- @endcan --}}

                                            {{-- @can('eliminar roles') --}}
                                                <a href="javascript:void(0)"
                                                   onclick="eliminarRol({{ $role->id }})"
                                                   class="btn btn-sm btn-danger">
                                                    Eliminar
                                                </a>
                                            {{-- @endcan --}}
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
@endpush
