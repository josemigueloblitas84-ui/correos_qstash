<x-app-layout>
    <x-slot name="header">
       <div class="flex justify-between items-center">
         <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Usuarios') }}
        </h2>
        @can('nuevo permiso')
            <a href="{{ route('roles.create') }}" class="ml-4 bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Crear Rol</a>
        @endcan

       </div>

    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

           @include('mensajes')

            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr class="border-b">
                        <th class="px-6 py-3 text-left" width="80">#</th>
                        <th class="px-6 py-3 text-left">Nombre</th>
                        <th class="px-6 py-3 text-left">Correo</th>
                        <th class="px-6 py-3 text-left">Rol</th>
                        <th class="px-6 py-3 text-left" width="200">Creación</th>
                        <th class="px-6 py-3 text-left" width="200">Acciones</th>
                    </tr>
                </thead>
                <tbody class="bg-white">
                    @if ($usuarios->isNotEmpty())
                        @foreach ($usuarios as $usuario)
                            <tr class="border-b">
                                <td class="px-6 py-4">{{ $usuario->id }}</td>
                                <td class="px-6 py-4">{{ $usuario->name }}</td>
                                <td class="px-6 py-4">{{ $usuario->email }}</td>
                                <td class="px-6 py-4">{{ $usuario->roles->pluck('name')->implode(', ') }}</td>
                                <td class="px-6 py-4">{{ $usuario->created_at->format('d / M / Y') }}</td>
                                <td class="px-6 py-4">
                                    @can('editar usuarios')
                                        <a href="{{ route('usuarios.edit', $usuario->id) }}" class="bg-slate-700 text-sm rounded-md text-white px-3 py-2 hover:bg-slate-600">Editar</a>
                                    @endcan
                                    {{-- <a href="javascript:void(0)" onclick="eliminarRol({{ $role->id }})" class="bg-red-700 text-sm rounded-md text-white px-3 py-2 hover:bg-red-600">Eliminar</a> --}}
                                </td>
                            </tr>
                        @endforeach
                    @endif
                </tbody>
            </table>
            <div class="mt-2">
                {{ $usuarios->links() }}
            </div>
        </div>
    </div>
    <x-slot name="script">
        <script type="text/javascript">
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
    </x-slot>
</x-app-layout>
