@extends('plantilla.app')

@section('title', 'Certificados')

@section('content')
    <div class="app-content-header py-3">
        <div class="container-fluid">
            <h2 class="mb-0">Certificados</h2>
        </div>
    </div>

    <div class="app-content">
        <div class="container-fluid">
            @if (session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Subir plantilla PDF</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('certificados.plantillas.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="mb-3">
                            <label for="nombre" class="form-label">Nombre de la plantilla</label>
                            <input
                                type="text"
                                name="nombre"
                                id="nombre"
                                class="form-control @error('nombre') is-invalid @enderror"
                                value="{{ old('nombre') }}"
                                placeholder="Ej. Certificado curso 2026"
                                required
                            >
                            @error('nombre')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="archivo_pdf" class="form-label">Archivo PDF</label>
                            <input
                                type="file"
                                name="archivo_pdf"
                                id="archivo_pdf"
                                class="form-control @error('archivo_pdf') is-invalid @enderror"
                                accept="application/pdf"
                                required
                            >
                            @error('archivo_pdf')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-primary">
                            Subir plantilla
                        </button>
                    </form>
                </div>
            </div>

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
                                        <th>Tamano</th>
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
                                            <td class="d-flex gap-2">
                                                <a href="{{ $plantilla['url_ver'] }}"
                                                target="_blank"
                                                class="btn btn-sm btn-outline-primary">
                                                    Ver PDF
                                                </a>

                                                <a href="{{ $plantilla['url_editar'] }}"
                                                class="btn btn-sm btn-outline-secondary">
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
