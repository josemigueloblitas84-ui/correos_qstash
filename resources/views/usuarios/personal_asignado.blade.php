@extends('plantilla.app')

@section('title', 'Asignar Personal')

@section('content')
    <div class="app-content-header">
        <div class="container-fluid d-flex justify-content-between align-items-center">
            <h2>Asignar Personal</h2>
            <a href="{{ route('usuarios.index') }}" class="btn btn-secondary">
                Volver
            </a>
        </div>
    </div>

    <div class="app-content">
        <div class="container-fluid">
            @include('mensajes')

            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-1">
                        Usuario responsable: <strong>{{ $usuario->name }}</strong>
                    </h5>
                    <small class="text-muted">{{ $usuario->email }}</small>
                </div>

                <div class="card-body">
                    <form action="{{ route('usuarios.personal.update', $usuario->id) }}" method="POST" id="formAsignarPersonal">
                        @csrf

                        <div class="mb-3">
                            <label for="usuarios_asignados_select" class="form-label fw-semibold">
                                Seleccionar personal
                            </label>

                            <select
                                name="usuarios_asignados[]"
                                id="usuarios_asignados_select"
                                class="form-select"
                                multiple
                            >
                                @foreach ($usuariosDisponibles as $item)
                                    <option
                                        value="{{ $item->id }}"
                                        data-email="{{ $item->email }}"
                                        data-departamento="{{ $item->departamento_nombre ?? 'Sin departamento' }}"
                                        data-tipo="{{ $item->tipo_personal_nombre ?? 'Sin tipo' }}"
                                        {{ in_array($item->id, $usuariosAsignados, true) ? 'selected' : '' }}
                                    >
                                        {{ $item->name }}
                                    </option>
                                @endforeach
                            </select>

                            @error('usuarios_asignados')
                                <div class="text-danger mt-2">{{ $message }}</div>
                            @enderror

                            @error('usuarios_asignados.*')
                                <div class="text-danger mt-2">{{ $message }}</div>
                            @enderror
                        </div>

                        <div id="usuariosSeleccionadosContainer" class="row g-3 mb-4"></div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary" id="btnAsignarPersonal">
                                Asignar
                            </button>

                            <a href="{{ route('usuarios.index') }}" class="btn btn-outline-secondary">
                                Cancelar
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/asignacion_personal.css') }}">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@endpush

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        window.personalAsignadoConfig = {
            successMessage: @json(session('success'))
        };
    </script>

    <script src="{{ asset('assets/js/asignacion_personal.js') }}"></script>
@endpush
