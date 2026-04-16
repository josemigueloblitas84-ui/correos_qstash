@extends('plantilla.app')

@section('content')
    <div class="app-content-header py-3">
        <div class="container-fluid">
            <h3 class="mb-0">Panel de control</h3>
        </div>
    </div>

    <div class="app-content">
        <div class="container-fluid">
            <div class="row g-3">
                <div class="col-lg-4 col-md-6">
                    <div class="small-box text-bg-success">
                        <div class="inner">
                            <h3>{{ $controlHorasSummary['horas_totales'] }}</h3>
                            <p>Total horas a cumplir</p>
                            <small>Horas planificadas</small>
                        </div>
                        <svg class="small-box-icon" fill="currentColor" viewBox="0 0 24 24"
                            xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                            <path
                                d="M5.625 3.75A2.625 2.625 0 003 6.375v11.25a2.625 2.625 0 002.625 2.625h12.75A2.625 2.625 0 0021 17.625V6.375a2.625 2.625 0 00-2.625-2.625H5.625zM12.53 14.78a.75.75 0 01-1.06 0l-2.25-2.25a.75.75 0 111.06-1.06L12 13.19l3.72-3.72a.75.75 0 111.06 1.06l-4.25 4.25z">
                            </path>
                        </svg>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6">
                    <div class="small-box text-bg-primary">
                        <div class="inner">
                            <h3>{{ $controlHorasSummary['horas_validadas'] }}</h3>
                            <p>Total horas realizadas</p>
                            <small>
                                @if ($controlHorasSummary['fecha_desde'] && $controlHorasSummary['fecha_hasta'])
                                    Del {{ $controlHorasSummary['fecha_desde'] }} al {{ $controlHorasSummary['fecha_hasta'] }}
                                @else
                                    Sin horas validadas registradas
                                @endif
                            </small>
                        </div>
                        <svg class="small-box-icon" fill="currentColor" viewBox="0 0 24 24"
                            xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                            <path
                                d="M12 6a.75.75 0 01.75.75v4.19l2.53 2.53a.75.75 0 11-1.06 1.06l-2.75-2.75A.75.75 0 0111.25 11V6.75A.75.75 0 0112 6z">
                            </path>
                            <path clip-rule="evenodd" fill-rule="evenodd"
                                d="M12 2.25a9.75 9.75 0 100 19.5 9.75 9.75 0 000-19.5zM3.75 12a8.25 8.25 0 1116.5 0 8.25 8.25 0 01-16.5 0z">
                            </path>
                        </svg>
                        <a
                            href="#"
                            class="small-box-footer link-light link-underline-opacity-0 link-underline-opacity-50-hover"
                            data-bs-toggle="modal"
                            data-bs-target="#modalControlHoras">
                            Ver Control de Horas <i class="bi bi-clock"></i>
                        </a>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6">
                    <div class="small-box text-bg-warning">
                        <div class="inner">
                            <h3>{{ $controlHorasSummary['horas_pendientes'] }}</h3>
                            <p>Total horas a ser realizadas</p>
                            <small>Horas pendientes</small>
                        </div>
                        <svg class="small-box-icon" fill="currentColor" viewBox="0 0 24 24"
                            xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                            <path clip-rule="evenodd" fill-rule="evenodd"
                                d="M12 2.25c-5.385 0-9.75 4.365-9.75 9.75s4.365 9.75 9.75 9.75 9.75-4.365 9.75-9.75S17.385 2.25 12 2.25zm.75 5.25a.75.75 0 00-1.5 0v5.25c0 .199.079.39.22.53l3 3a.75.75 0 101.06-1.06l-2.78-2.78V7.5z">
                            </path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="card dashboard-hours-table-card mt-4">
                <div class="card-header dashboard-hours-table-card__header">
                    <div>
                        <h5 class="mb-0">Detalle de control de horas</h5>
                        <small>Registro diario de actividades realizadas</small>
                    </div>
                    {{-- <span class="dashboard-hours-table-card__badge">Vista preliminar</span> --}}
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table dashboard-hours-table align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>No.</th>
                                    <th>Fecha</th>
                                    <th>Hora entrada</th>
                                    <th>Hora salida</th>
                                    <th>Horas realizadas</th>
                                    <th>Actividad(es) realizada(s)</th>
                                    <th>Horario</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($controlHorasRows as $registro)
                                    <tr>
                                        <td class="text-center">{{ $loop->iteration }}</td>
                                        <td class="text-center">{{ $registro['fecha'] }}</td>
                                        <td class="text-center">{{ $registro['hora_inicio'] }}</td>
                                        <td class="text-center">{{ $registro['hora_fin'] }}</td>
                                        <td class="text-center fw-semibold">{{ $registro['total_hora'] }}</td>
                                        <td>
                                            <select
                                                class="form-select form-select-sm dashboard-hours-activity-select"
                                                aria-label="Actividades realizadas el {{ $registro['fecha'] }}">
                                                @forelse ($registro['actividades'] as $actividad)
                                                    <option>{{ $actividad['texto'] }}</option>
                                                @empty
                                                    <option>Sin actividades registradas</option>
                                                @endforelse
                                            </select>
                                        </td>
                                        <td class="text-center">
                                            <span class="dashboard-hours-status">{{ $registro['horario'] }}</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-4">
                                            No hay registros de horas validadas.
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

    <div class="modal fade" id="modalControlHoras" tabindex="-1" aria-labelledby="modalControlHorasLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content control-horas-modal">
                <div class="modal-header control-horas-modal__header">
                    <h5 class="modal-title" id="modalControlHorasLabel">Control de horas</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body control-horas-modal__body">
                    <iframe
                        src="{{ route('dashboard.control-horas.preview') }}"
                        class="control-horas-frame"
                        title="Vista previa control de horas">
                    </iframe>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .dashboard-hours-table-card {
            border: 0;
            border-radius: 8px;
            box-shadow: 0 8px 22px rgba(31, 41, 51, 0.08);
            overflow: hidden;
        }

        .dashboard-hours-table-card__header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            background: #ffffff;
            border-bottom: 1px solid #e4eaf0;
            padding: 1rem 1.25rem;
        }

        .dashboard-hours-table-card__header h5 {
            color: #1f2933;
            font-weight: 700;
        }

        .dashboard-hours-table-card__header small {
            color: #6b7280;
        }

        .dashboard-hours-table-card__badge {
            flex-shrink: 0;
            border-radius: 999px;
            background: #e8f3fb;
            color: #1f78ad;
            font-size: 0.78rem;
            font-weight: 700;
            padding: 0.35rem 0.75rem;
            text-transform: uppercase;
        }

        .dashboard-hours-table {
            min-width: 980px;
            border-color: #d9e1e8;
        }

        .dashboard-hours-table thead th {
            background: #1f78ad;
            color: #ffffff;
            font-size: 0.92rem;
            font-weight: 700;
            text-align: center;
            vertical-align: middle;
            white-space: nowrap;
        }

        .dashboard-hours-table tbody td {
            border-color: #dfe6ed;
            color: #17212b;
            vertical-align: middle;
        }

        .dashboard-hours-table tbody tr:nth-child(even) {
            background: #f8fbfd;
        }

        .dashboard-hours-activity-select {
            min-width: 360px;
            border-color: #d4dce5;
            color: #374151;
            box-shadow: none;
        }

        .dashboard-hours-activity-select:focus {
            border-color: #1f78ad;
            box-shadow: 0 0 0 0.2rem rgba(31, 120, 173, 0.12);
        }

        .dashboard-hours-status {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 76px;
            border-radius: 999px;
            background: #eef7ee;
            color: #197a34;
            font-size: 0.82rem;
            font-weight: 700;
            padding: 0.3rem 0.7rem;
        }

        .control-horas-modal {
            border: 0;
            border-radius: 10px;
            overflow: hidden;
        }

        .control-horas-modal__header {
            background: #1f78ad;
            color: #ffffff;
            border-bottom: 0;
        }

        .control-horas-modal__header .modal-title {
            font-weight: 700;
            text-transform: uppercase;
        }

        .control-horas-modal__header .btn-close {
            filter: invert(1);
            opacity: 0.9;
        }

        .control-horas-modal__body {
            background: #f4f6f9;
            padding: 1rem;
        }

        .control-horas-frame {
            width: 100%;
            min-height: 78vh;
            border: 0;
            background: #ffffff;
            box-shadow: inset 0 0 0 1px rgba(31, 41, 51, 0.12);
        }

        @media (max-width: 991.98px) {
            .dashboard-hours-table-card__header {
                align-items: flex-start;
                flex-direction: column;
            }

            .control-horas-frame {
                min-height: 70vh;
            }
        }
    </style>
@endpush
