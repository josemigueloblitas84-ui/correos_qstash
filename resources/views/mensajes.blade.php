{{-- resources/views/mensajes.blade.php --}}
@if (session('success'))
    <div
        x-data="{ show: true }"
        x-init="setTimeout(() => show = false, 3500)"
        x-show="show"
        x-transition.opacity.duration.300ms
        class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4"
        role="alert"
    >
        <button type="button" class="absolute top-2 right-3 font-bold" @click="show = false">&times;</button>
        <strong class="font-bold">Exito:</strong>
        <span class="block sm:inline pr-6">{{ session('success') }}</span>
    </div>
@endif

@if (session('error'))
    <div
        x-data="{ show: true }"
        x-init="setTimeout(() => show = false, 3500)"
        x-show="show"
        x-transition.opacity.duration.300ms
        class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4"
        role="alert"
    >
        <button type="button" class="absolute top-2 right-3 font-bold" @click="show = false">&times;</button>
        <strong class="font-bold">Error:</strong>
        <span class="block sm:inline pr-6">{{ session('error') }}</span>
    </div>
@endif
