@extends('plantilla.app')

@section('title', 'Mis Certificados')

@section('content')
    <div class="app-content-header py-3">
        <div class="container-fluid d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div>
                <h2 class="mb-1">Mis certificados</h2>
                <small class="text-muted">Emite una sola vez y descarga tu PDF hasta 5 veces.</small>
            </div>

            <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">
                Volver al dashboard
            </a>
        </div>
    </div>

    <div class="app-content">
        <div class="container-fluid">
            @include('mensajes')

            <div class="card shadow-sm border-0">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Certificados disponibles</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Plantilla</th>
                                    <th>Estado</th>
                                    <th>Fecha de emision</th>
                                    <th>Descargas</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($items as $item)
                                    <tr>
                                        <td>
                                            <div class="fw-semibold">{{ $item['nombre'] }}</div>
                                            <small class="text-muted">{{ $item['archivo'] }}</small>
                                        </td>
                                        <td>
                                            @if ($item['emitido'])
                                                <span class="badge text-bg-success">Emitido</span>
                                            @else
                                                <span class="badge text-bg-secondary">Pendiente</span>
                                            @endif
                                        </td>
                                        <td>
                                            {{ $item['fecha_emision'] ? \Carbon\Carbon::parse($item['fecha_emision'])->format('d/m/Y H:i') : 'Aun no emitido' }}
                                        </td>
                                        <td>
                                            <span class="fw-semibold">{{ $item['cantidad_descargas'] }}</span>
                                            <small class="text-muted">/ 5</small>
                                            <div class="small text-muted">
                                                Restantes: {{ $item['descargas_restantes'] }}
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <div class="d-flex flex-wrap justify-content-center gap-2">
                                                @if ($item['emitido'])
                                                    <button type="button" class="btn btn-secondary btn-sm" disabled>
                                                        Emitido
                                                    </button>
                                                @else
                                                    <a href="{{ $item['emit_url'] }}" class="btn btn-primary btn-sm">
                                                        Emitir certificado
                                                    </a>
                                                @endif

                                                @if ($item['download_url'])
                                                    <a href="{{ $item['download_url'] }}" class="btn btn-warning btn-sm">
                                                        Descargar PDF
                                                    </a>
                                                @else
                                                    <button type="button" class="btn btn-outline-warning btn-sm" disabled>
                                                        Descargar PDF
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-4">
                                            No hay certificados disponibles por el momento.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
