@extends('plantilla.app')

@section('title', 'Informe de Agenda')

@section('content')
<div class="app-content-header py-3">
    <div class="container-fluid">
        <h2 class="mb-0">Informe de Agenda</h2>
    </div>
</div>

<div class="app-content informe-page">
    <div class="container-fluid">
        @include('mensajes')

        <form method="POST" action="{{ route('informe-agenda.store') }}">
            @csrf
            <input type="hidden" name="fecha" value="{{ $fecha }}">

            <div class="informe-card">
                <div class="informe-card__header informe-card__header--diario">
                    Informe actividad diaria - {{ $fechaTexto }}
                </div>

                <div class="informe-table-wrap">
                    <table class="informe-table">
                        <thead>
                            <tr>
                                <th class="informe-col-numero">No.</th>
                                <th class="informe-col-actividad">Actividad programada</th>
                                <th class="informe-col-realizado">Realizado ?</th>
                                <th class="informe-col-detalle">Detalle de actividad realizadas o no realizadas</th>
                                <th class="informe-col-equipo">Equipo</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($actividadesDiarias as $actividad)
                                @php($rowKey = 'actividad_' . $actividad->agenda_actividad_id)
                                <tr>
                                    <td class="informe-row-number">{{ $loop->iteration }}</td>
                                    <td>
                                        <div class="informe-actividad-texto">{{ $actividad->actividad }}</div>
                                        <input type="hidden" name="programadas[{{ $rowKey }}][agenda_actividad_id]" value="{{ $actividad->agenda_actividad_id }}">
                                        <input type="hidden" name="programadas[{{ $rowKey }}][agenda_id]" value="{{ $actividad->agenda_id }}">
                                        <input type="hidden" name="programadas[{{ $rowKey }}][actividad]" value="{{ $actividad->actividad }}">
                                        <input type="hidden" name="programadas[{{ $rowKey }}][departamento_id]" value="{{ $actividad->departamento_id }}">
                                        <input type="hidden" name="programadas[{{ $rowKey }}][tipo_actividad]" value="D">
                                    </td>
                                    <td>
                                        <div class="informe-switch-wrap">
                                            <label class="informe-switch">
                                                <input
                                                    type="checkbox"
                                                    name="programadas[{{ $rowKey }}][estado]"
                                                    value="1"
                                                    {{ (int) $actividad->informe_estado === 1 ? 'checked' : '' }}
                                                >
                                                <span class="informe-slider"></span>
                                            </label>
                                        </div>
                                        <div class="informe-switch-labels">
                                            <span>NO</span>
                                            <span>SI</span>
                                        </div>
                                    </td>
                                    <td>
                                        <textarea class="informe-textarea" name="programadas[{{ $rowKey }}][detalle_estado]" placeholder="Detalle de la actividad no realizada">{{ $actividad->informe_detalle }}</textarea>
                                    </td>
                                    <td>
                                        <div class="informe-actividad-texto">{{ $actividad->equipo }}</div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4">No hay actividades diarias para la fecha.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="informe-card">
                <div class="informe-card__header informe-card__header--semanal">
                    Informe actividades semanales para la fecha - {{ $fechaTexto }}
                </div>

                <div class="informe-table-wrap">
                    <table class="informe-table">
                        <thead>
                            <tr>
                                <th class="informe-col-numero">No.</th>
                                <th class="informe-col-actividad">Actividad programada</th>
                                <th class="informe-col-realizado">Realizado ?</th>
                                <th class="informe-col-detalle">Detalle de actividad realizadas o no realizadas</th>
                                <th class="informe-col-equipo">Equipo</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($actividadesSemanales as $actividad)
                                @php($rowKey = 'actividad_' . $actividad->agenda_actividad_id)
                                <tr>
                                    <td class="informe-row-number">{{ $loop->iteration }}</td>
                                    <td>
                                        <div class="informe-actividad-texto">{{ $actividad->actividad }}</div>
                                        <input type="hidden" name="programadas[{{ $rowKey }}][agenda_actividad_id]" value="{{ $actividad->agenda_actividad_id }}">
                                        <input type="hidden" name="programadas[{{ $rowKey }}][agenda_id]" value="{{ $actividad->agenda_id }}">
                                        <input type="hidden" name="programadas[{{ $rowKey }}][actividad]" value="{{ $actividad->actividad }}">
                                        <input type="hidden" name="programadas[{{ $rowKey }}][departamento_id]" value="{{ $actividad->departamento_id }}">
                                        <input type="hidden" name="programadas[{{ $rowKey }}][tipo_actividad]" value="S">
                                    </td>
                                    <td>
                                        <div class="informe-switch-wrap">
                                            <label class="informe-switch">
                                                <input
                                                    type="checkbox"
                                                    name="programadas[{{ $rowKey }}][estado]"
                                                    value="1"
                                                    {{ (int) $actividad->informe_estado === 1 ? 'checked' : '' }}
                                                >
                                                <span class="informe-slider"></span>
                                            </label>
                                        </div>
                                        <div class="informe-switch-labels">
                                            <span>NO</span>
                                            <span>SI</span>
                                        </div>
                                    </td>
                                    <td>
                                        <textarea class="informe-textarea" name="programadas[{{ $rowKey }}][detalle_estado]" placeholder="Detalle de la actividad no realizada">{{ $actividad->informe_detalle }}</textarea>
                                    </td>
                                    <td>
                                        <div class="informe-actividad-texto">{{ $actividad->equipo }}</div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4">No hay actividades semanales para la fecha.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="informe-card">
                <div class="informe-card__header informe-card__header--extra">
                    <span>Actividades no programadas para la fecha - {{ $fechaTexto }}</span>
                    <button type="button" class="informe-add-btn" id="btnAgregarActividad">
                        Añadir actividad
                    </button>
                </div>

                <div class="informe-table-wrap">
                    <table class="informe-table" id="tablaActividadesNoProgramadas">
                        <thead>
                            <tr>
                                <th class="informe-col-numero">No.</th>
                                <th>Actividad realizada</th>
                                <th class="informe-col-equipo">Equipo</th>
                                <th class="informe-col-acciones">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($actividadesNoProgramadas as $actividad)
                                <tr>
                                    <td class="informe-row-number">{{ $loop->iteration }}</td>
                                    <td>
                                        <span class="informe-actividad-texto">{{ $actividad->actividad }}</span>
                                        <input type="hidden" data-field="agenda_id" value="{{ $actividad->agenda_id }}">
                                        <input type="hidden" data-field="actividad" value="{{ $actividad->actividad }}">
                                        <input type="hidden" data-field="departamento_id" value="{{ $actividad->departamento_id }}">
                                    </td>
                                    <td>
                                        <span class="informe-actividad-texto">{{ $actividad->equipo }}</span>
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="informe-btn-remove btnEliminarFila">Quitar</button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="informe-footer">
                <button type="submit" class="informe-save-btn">
                    Guardar informe
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/informe-agenda.css') }}">
@endpush

@push('scripts')
<script>
    window.informeAgendaConfig = {
        agendaIdActual: @json($agendaIdActual),
        departamentos: @json($departamentos),
        fecha: @json($fecha),
        storeNoProgramadaUrl: @json(route('informe-agenda.no-programada.store')),
    };
</script>
<script src="{{ asset('assets/js/informe-agenda.js') }}"></script>
@endpush
