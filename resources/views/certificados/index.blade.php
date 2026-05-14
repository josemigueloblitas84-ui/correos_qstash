@extends('plantilla.app')

@section('title', 'Certificados')

@section('content')
    <div class="app-content-header py-3">
        <div class="container-fluid d-flex justify-content-between align-items-center gap-3 flex-wrap">
            <h2 class="mb-0">Certificados</h2>

            <a href="{{ route('certificados.create') }}" class="btn btn-primary">
                Crear plantilla
            </a>
        </div>
    </div>

    <div class="app-content">
        <div class="container-fluid">
            @if (session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0">Plantillas disponibles</h5>
                </div>
                <div class="card-body">
                    @if (empty($plantillas))
                        <p class="text-muted mb-0">Aun no hay plantillas subidas.</p>
                    @else
                        <div class="table-responsive">
                            <table class="table table-bordered align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Archivo</th>
                                        <th>Tamaño</th>
                                        <th>Fecha</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($plantillas as $plantilla)
                                        <tr>
                                            <td>{{ $plantilla['nombre_archivo'] }}</td>
                                            <td>{{ $plantilla['tamano_kb'] }} KB</td>
                                            <td>{{ $plantilla['fecha'] }}</td>
                                            <td>
                                                <a href="{{ $plantilla['url_ver'] }}"
                                                target="_blank"
                                                class="btn btn-primary btn-sm">
                                                    Ver PDF
                                                </a>

                                                <a href="{{ $plantilla['url_editar'] }}"
                                                class="btn btn-warning btn-sm">
                                                    Editar
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
