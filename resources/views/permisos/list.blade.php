<x-app-layout>
    <x-slot name="header">
       <div class="flex justify-between items-center">
         <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Permisos') }}
        </h2>
        @can('crear permisos')
        <a href="{{ route('permisos.create') }}" class="ml-4 bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Crear Permiso</a>
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
                        <th class="px-6 py-3 text-left" width="200">Creación</th>
                        <th class="px-6 py-3 text-left" width="200">Acciones</th>
                    </tr>
                </thead>
                <tbody class="bg-white">
                    @if ($permisos->isNotEmpty())
                        @foreach ($permisos as $permiso)
                            <tr class="border-b">
                                <td class="px-6 py-4">{{ $permiso->id }}</td>
                                <td class="px-6 py-4">{{ $permiso->name }}</td>
                                <td class="px-6 py-4">{{ $permiso->created_at->format('d / M / Y') }}</td>
                                <td class="px-6 py-4">
                                    @can('editar permisos')
                                         <a href="{{ route('permisos.edit', $permiso->id) }}" class="bg-slate-700 text-sm rounded-md text-white px-3 py-2 hover:bg-slate-600">Editar</a>
                                    @endcan
                                    @can('eliminar permisos')
                                          <a href="javascript:void(0)" onclick="eliminarPermiso({{ $permiso->id }})" class="bg-red-700 text-sm rounded-md text-white px-3 py-2 hover:bg-red-600">Eliminar</a>
                                    @endcan
                                </td>
                            </tr>
                        @endforeach
                    @endif
                </tbody>
            </table>
            <div class="mt-2">
                {{ $permisos->links() }}
            </div>
        </div>
    </div>
    <x-slot name="script">
        <script type="text/javascript">
            function eliminarPermiso(id) {
                if (confirm('¿Desea eliminar el permiso?')) {
                    $.ajax({
                        url: '{{ route("permisos.destroy") }}',
                        type: 'DELETE',
                        data: {id:id},
                        dataType: 'json',
                        headers: {
                            'X-CSRF-TOKEN': "{{ csrf_token() }}"
                        },
                        success: function(response) {
                            window.location.href = '{{ route("permisos.index") }}';
                        }
                    });
                }
            }
        </script>
    </x-slot>
</x-app-layout>
