{{--  --}}@php
    use Illuminate\Support\Carbon;

    $formatDate = static fn ($value) => $value ? Carbon::parse($value)->format('d-m-Y') : '';
    $formatTime = static fn ($value) => $value ? substr((string) $value, 0, 5) : '';
    $formatWeekExecution = static function ($desde, $hasta) use ($formatDate) {
        $desdeFormatted = $formatDate($desde);
        $hastaFormatted = $formatDate($hasta);

        if (! $desdeFormatted && ! $hastaFormatted) {
            return '';
        }

        if ($desdeFormatted === $hastaFormatted || ! $hastaFormatted) {
            return $desdeFormatted;
        }

        return $desdeFormatted . ' al ' . $hastaFormatted;
    };

    $monthNames = [
        1 => 'Enero',
        2 => 'Febrero',
        3 => 'Marzo',
        4 => 'Abril',
        5 => 'Mayo',
        6 => 'Junio',
        7 => 'Julio',
        8 => 'Agosto',
        9 => 'Septiembre',
        10 => 'Octubre',
        11 => 'Noviembre',
        12 => 'Diciembre',
    ];

    $footerDate = '';

    if (! empty($agenda->fecha)) {
        $footer = Carbon::parse($agenda->fecha);
        $footerDate = 'La Paz, ' . $footer->day . ' de ' . $monthNames[$footer->month] . ' de ' . $footer->year;
    }
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Agenda semanal</title>
    <style>
        @page {
            margin: 22px 24px 28px 24px;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            color: #111111;
            margin: 0;
            font-size: 11px;
        }

        .document-frame {
            padding: 18px 18px 20px;
        }

        .brand {
            text-align: center;
            margin-bottom: 14px;
        }

        .brand img {
            width: 104px;
            margin-bottom: 6px;
        }

        .title {
            font-size: 17px;
            font-weight: 700;
            letter-spacing: 0.04em;
        }

        .summary {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }

        .summary th,
        .summary td {
            padding: 2px 4px;
            vertical-align: top;
            font-size: 11px;
        }

        .summary th {
            width: 26%;
            text-align: left;
            font-weight: 700;
            white-space: nowrap;
        }

        .summary td {
            width: 24%;
        }

        .grid {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .grid th,
        .grid td {
            border: 1.5px solid #1f1f1f;
            padding: 3px 5px;
            vertical-align: top;
            font-size: 10.5px;
            line-height: 1.2;
        }

        .grid thead th,
        .grid .subhead th {
            text-align: center;
            font-weight: 700;
        }

        .number-col {
            width: 8%;
            text-align: center;
        }

        .description-col {
            width: 56%;
        }

        .execution-col {
            width: 19%;
            text-align: center;
        }

        .department-col {
            width: 17%;
        }

        .section td {
            color: #1d49d7;
            font-weight: 700;
            font-size: 11px;
            padding-top: 4px;
            padding-bottom: 4px;
        }

        .text-center {
            text-align: center;
        }

        .footer {
            margin-top: 22px;
            font-size: 11px;
        }
    </style>
</head>
<body>
    <div class="document-frame">
        <div class="brand">
            @if (! empty($logoDataUri))
                <img src="{{ $logoDataUri }}" alt="Logo institucional">
            @endif
            <div class="title">AGENDA SEMANAL</div>
        </div>

        <table class="summary">
            <tr>
                <th>UNIDAD O DEPARTAMENTO:</th>
                <td>{{ $agenda->departamento }}</td>
                <th></th>
                <td></td>
            </tr>
            <tr>
                <th>NOMBRE:</th>
                <td>{{ $agenda->solicitante }}</td>
                <th></th>
                <td></td>
            </tr>
            <tr>
                <th>PERIODO:</th>
                <td>{{ $formatDate($agenda->fecha_desde) }} al {{ $formatDate($agenda->fecha_hasta) }}</td>
                <th>HORARIO:</th>
                <td>De {{ $formatTime($agenda->hora_desde) }} a {{ $formatTime($agenda->hora_hasta) }}</td>
            </tr>
        </table>

        <table class="grid">
            <thead>
                <tr>
                    <th class="number-col">No.</th>
                    <th class="description-col">DESCRIPCION</th>
                    <th class="execution-col">HORAS EJECUCION</th>
                    <th class="department-col">DEPARTAMENTO</th>
                </tr>
            </thead>
            <tbody>
                <tr class="section">
                    <td colspan="4">ACTIVIDADES DIARIAS</td>
                </tr>
                @forelse ($dailyActivities as $index => $activity)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>{{ $activity->actividad }}</td>
                        <td class="text-center">
                            {{ $formatTime($activity->hora_desde_actividad) }}
                            @if ($activity->hora_hasta_actividad)
                                a {{ $formatTime($activity->hora_hasta_actividad) }}
                            @endif
                        </td>
                        <td>{{ $activity->equipo ?: 'Sin departamento' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td class="text-center">-</td>
                        <td colspan="3">No hay actividades diarias registradas.</td>
                    </tr>
                @endforelse

                <tr class="subhead">
                    <th class="number-col">No.</th>
                    <th class="description-col">DESCRIPCION</th>
                    <th class="execution-col">FECHAS EJECUCION</th>
                    <th class="department-col">DEPARTAMENTO</th>
                </tr>
                <tr class="section">
                    <td colspan="4">ACTIVIDADES EN LA SEMANA</td>
                </tr>
                @forelse ($weeklyActivities as $index => $activity)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>{{ $activity->actividad }}</td>
                        <td class="text-center">{{ $formatWeekExecution($activity->fecha_del, $activity->fecha_hasta) }}</td>
                        <td>{{ $activity->equipo ?: 'Sin departamento' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td class="text-center">-</td>
                        <td colspan="3">No hay actividades semanales registradas.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @if ($footerDate)
            <div class="footer">{{ $footerDate }}</div>
        @endif
    </div>
</body>
</html>
