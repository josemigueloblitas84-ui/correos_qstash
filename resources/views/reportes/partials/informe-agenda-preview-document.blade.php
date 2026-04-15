@php
    use Illuminate\Support\Carbon;

    $formatDate = static fn ($value) => $value ? Carbon::parse($value)->format('d-m-Y') : '';
    $estadoTexto = static fn ($value) => (int) $value === 1 ? 'SI' : 'NO';

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

    if (! empty($informe->fecha_actividad)) {
        $footer = Carbon::parse($informe->fecha_actividad);
        $footerDate = 'La Paz, ' . $footer->day . ' de ' . $monthNames[$footer->month] . ' de ' . $footer->year;
    }
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Informe de Agenda</title>
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
            border: 4px solid #1f1f1f;
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

        .grid thead th {
            text-align: center;
            font-weight: 700;
        }

        .number-col {
            width: 6%;
            text-align: center;
        }

        .description-col {
            width: 34%;
        }

        .execution-col {
            width: 10%;
            text-align: center;
        }

        .comment-col {
            width: 36%;
        }

        .department-col {
            width: 14%;
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
            <div class="title">INFORME DE AGENDA</div>
        </div>

        <table class="summary">
            <tr>
                <th>UNIDAD O DEPARTAMENTO:</th>
                <td>{{ $informe->equipo_nombre }}</td>
                <th></th>
                <td></td>
            </tr>
            <tr>
                <th>NOMBRE:</th>
                <td>{{ $informe->usuario_nombre }}</td>
                <th></th>
                <td></td>
            </tr>
            <tr>
                <th>PERIODO:</th>
                <td>{{ $formatDate($informe->fecha_actividad) }}</td>
                <th></th>
                <td></td>
            </tr>
        </table>

        <table class="grid">
            <thead>
                <tr>
                    <th class="number-col">No.</th>
                    <th class="description-col">DESCRIPCIÓN</th>
                    <th class="execution-col">EJECUCIÓN</th>
                    <th class="comment-col">COMENTARIOS U OBSERVACIONES</th>
                    <th class="department-col">EQUIPO</th>
                </tr>
            </thead>
            <tbody>
                <tr class="section">
                    <td colspan="5">ACTIVIDADES DIARIAS</td>
                </tr>
                @forelse ($actividadesDiarias as $index => $actividad)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>{{ $actividad->actividad }}</td>
                        <td class="text-center">{{ $estadoTexto($actividad->estado) }}</td>
                        <td>{{ $actividad->detalle_estado }}</td>
                        <td>{{ $actividad->equipo_nombre }}</td>
                    </tr>
                @empty
                    <tr>
                        <td class="text-center">-</td>
                        <td colspan="4">No hay actividades diarias registradas.</td>
                    </tr>
                @endforelse

                <tr class="section">
                    <td colspan="5">ACTIVIDADES EN LA SEMANA</td>
                </tr>
                @forelse ($actividadesSemanales as $index => $actividad)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>{{ $actividad->actividad }}</td>
                        <td class="text-center">{{ $estadoTexto($actividad->estado) }}</td>
                        <td>{{ $actividad->detalle_estado }}</td>
                        <td>{{ $actividad->equipo_nombre }}</td>
                    </tr>
                @empty
                    <tr>
                        <td class="text-center">-</td>
                        <td colspan="4">No hay actividades semanales registradas.</td>
                    </tr>
                @endforelse

                <tr class="section">
                    <td colspan="5">ACTIVIDADES NO PROGRAMADAS</td>
                </tr>
                @forelse ($actividadesNoProgramadas as $index => $actividad)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>{{ $actividad->actividad }}</td>
                        <td class="text-center">{{ $estadoTexto($actividad->estado) }}</td>
                        <td>{{ $actividad->detalle_estado }}</td>
                        <td>{{ $actividad->equipo_nombre }}</td>
                    </tr>
                @empty
                    <tr>
                        <td class="text-center">-</td>
                        <td colspan="4">No hay actividades no programadas registradas.</td>
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
