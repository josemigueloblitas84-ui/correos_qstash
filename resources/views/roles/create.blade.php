<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Roles / Crear
            </h2>
            <a href="{{ route('roles.index') }}"
                class="ml-4 bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Volver</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form action="{{ route('roles.store') }}" method="POST">
                        @csrf
                        <div>
                            <label for="name" class="text-lg font-medium">Nombre:</label>
                            <div class="my-3">
                                <input value="{{ old('name') }}" name="name" id="name" type="text"
                                    placeholder="Ingrese el nombre" class="border-gray-300 shadow-sm w-1/2 rounded-lg">
                                @error('name')
                                    <p class="text-red-400 font-medium">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="grid grid-cols-4 my-5">
                                @if ($permisos->isNotEmpty())
                                    @foreach ($permisos as $permiso)
                                        <div class="mt-3">
                                            <input type="checkbox" id="permiso-{{ $permiso->id }}" name="permisos[]" value="{{ $permiso->name }}"
                                                class="rounded">
                                            <label for="permiso-{{ $permiso->id }}" class="ml-2">{{ $permiso->name }}</label>
                                        </div>
                                    @endforeach
                                @endif
                            </div>

                            <button type="submit"
                                class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 me-2 mb-2 dark:bg-blue-600 dark:hover:bg-blue-700 focus:outline-none dark:focus:ring-blue-800">Enviar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
