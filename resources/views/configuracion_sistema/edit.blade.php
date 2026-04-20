@extends('plantilla.app')

@section('title', 'Configuracion del Sistema')

@section('content')
    <div class="app-content-header py-3">
        <div class="container-fluid">
            <h3 class="mb-0">Configuracion del Sistema</h3>
        </div>
    </div>

    <div class="app-content">
        <div class="container-fluid">
            @if (!session('success'))
                @include('mensajes')
            @endif

            <form action="{{ route('configuracion-sistema.update') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="card border-0 shadow-sm overflow-hidden">
                    <div class="card-header bg-white border-0 px-4 pt-4 pb-0">
                        <div class="d-flex flex-column flex-lg-row align-items-lg-start justify-content-between gap-3">
                            <div>
                                <h4 class="mb-1">Datos institucionales</h4>
                                <p class="text-muted mb-0">Actualiza la informacion visible en el sistema, menu y reportes.</p>
                            </div>

                            <button type="submit" class="btn btn-primary px-4">
                                <i class="fas fa-save me-2"></i>Guardar configuracion
                            </button>
                        </div>
                    </div>

                    <div class="card-body p-4 pt-4">
                        <div class="row g-4">
                            <div class="col-lg-7">
                                <div class="row g-3">
                                    <div class="col-12">
                                        <label for="nombre_institucion" class="form-label fw-semibold">Nombre de la institucion</label>
                                        <input
                                            type="text"
                                            id="nombre_institucion"
                                            name="nombre_institucion"
                                            value="{{ old('nombre_institucion', $configuracion->nombre_institucion) }}"
                                            class="form-control @error('nombre_institucion') is-invalid @enderror"
                                            placeholder="Ingrese el nombre institucional">
                                        @error('nombre_institucion')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label for="correo_institucional" class="form-label fw-semibold">Correo institucional</label>
                                        <input
                                            type="email"
                                            id="correo_institucional"
                                            name="correo_institucional"
                                            value="{{ old('correo_institucional', $configuracion->correo_institucional) }}"
                                            class="form-control @error('correo_institucional') is-invalid @enderror"
                                            placeholder="correo@institucion.com">
                                        @error('correo_institucional')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label for="celular_institucional" class="form-label fw-semibold">Celular institucional</label>
                                        <input
                                            type="text"
                                            id="celular_institucional"
                                            name="celular_institucional"
                                            value="{{ old('celular_institucional', $configuracion->celular_institucional) }}"
                                            class="form-control @error('celular_institucional') is-invalid @enderror"
                                            placeholder="Ingrese el celular institucional">
                                        @error('celular_institucional')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label for="logo_principal" class="form-label fw-semibold">Logo principal</label>
                                        <input
                                            type="file"
                                            id="logo_principal"
                                            name="logo_principal"
                                            accept=".jpg,.jpeg,.png,.webp"
                                            class="form-control @error('logo_principal') is-invalid @enderror">
                                        <div class="form-text">Se usa en el menu lateral. Dejalo vacio si no deseas cambiarlo.</div>
                                        @error('logo_principal')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label for="logo_pdf" class="form-label fw-semibold">Logo para PDF</label>
                                        <input
                                            type="file"
                                            id="logo_pdf"
                                            name="logo_pdf"
                                            accept=".jpg,.jpeg,.png,.webp"
                                            class="form-control @error('logo_pdf') is-invalid @enderror">
                                        <div class="form-text">Se usa en agendas e informes en PDF. Dejalo vacio si no deseas cambiarlo.</div>
                                        @error('logo_pdf')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-5">
                                <div class="row g-3">
                                    <div class="col-12">
                                        <div class="border rounded-3 p-3 bg-light h-100">
                                            <div class="small text-uppercase fw-semibold text-muted mb-2">Vista actual del sistema</div>
                                            <div class="d-flex align-items-center gap-3">
                                                <img
                                                    src="{{ $presentacion['logo_principal_url'] }}"
                                                    alt="{{ $presentacion['nombre_institucion'] }}"
                                                    style="width: 64px; height: 64px; object-fit: contain;"
                                                    class="bg-white rounded-3 border p-2">
                                                <div>
                                                    <div class="fw-semibold">{{ $presentacion['nombre_institucion'] }}</div>
                                                    <div class="text-muted small">{{ $presentacion['correo_institucional'] ?: 'Sin correo institucional configurado' }}</div>
                                                    <div class="text-muted small">{{ $presentacion['celular_institucional'] ?: 'Sin celular institucional configurado' }}</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6 col-lg-12">
                                        <div class="border rounded-3 p-3 h-100">
                                            <div class="small text-uppercase fw-semibold text-muted mb-2">Logo principal actual</div>
                                            <div class="d-flex align-items-center justify-content-center bg-light rounded-3 border" style="min-height: 170px;">
                                                <img
                                                    src="{{ $presentacion['logo_principal_url'] }}"
                                                    alt="Logo principal"
                                                    style="max-width: 100%; max-height: 130px; object-fit: contain;">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6 col-lg-12">
                                        <div class="border rounded-3 p-3 h-100">
                                            <div class="small text-uppercase fw-semibold text-muted mb-2">Logo PDF actual</div>
                                            <div class="d-flex align-items-center justify-content-center bg-light rounded-3 border" style="min-height: 170px;">
                                                <img
                                                    src="{{ $presentacion['logo_pdf_url'] }}"
                                                    alt="Logo PDF"
                                                    style="max-width: 100%; max-height: 130px; object-fit: contain;">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    @if (session('success'))
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                Swal.fire({
                    icon: 'success',
                    title: 'Configuracion guardada',
                    text: @json(session('success')),
                    confirmButtonText: 'Aceptar',
                    timer: 2600,
                    timerProgressBar: true
                });
            });
        </script>
    @endif
@endpush
