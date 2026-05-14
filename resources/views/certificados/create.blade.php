@extends('plantilla.app')

@section('title', 'Crear Plantilla de Certificado')

@section('content')
    <div class="app-content-header py-3">
        <div class="container-fluid d-flex justify-content-between align-items-center gap-3 flex-wrap">
            <div>
                <h2 class="mb-0">Crear plantilla de certificado</h2>
            </div>

            <a href="{{ route('certificados.index') }}" class="btn btn-outline-secondary">
                Volver al listado
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

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="card shadow-sm">
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
        </div>
    </div>
@endsection
