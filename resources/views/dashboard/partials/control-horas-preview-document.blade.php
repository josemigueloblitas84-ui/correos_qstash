<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Control de horas</title>
    <style>
        @page {
            margin: 18px 20px;
        }

        body {
            margin: 0;
            font-family: Arial, Helvetica, sans-serif;
            color: #111111;
            font-size: 9px;
        }

        .sheet {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .sheet th,
        .sheet td {
            border: 1.2px solid #222222;
            padding: 4px 4px;
            vertical-align: middle;
        }

        .title-block {
            text-align: center;
            font-weight: 700;
            font-size: 10px;
            line-height: 1.15;
            text-transform: uppercase;
        }

        .main-title {
            text-align: center;
            font-size: 14px;
            font-weight: 700;
            letter-spacing: 0;
            text-transform: uppercase;
        }

        .section-title {
            text-align: center;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .label {
            font-weight: 700;
            text-transform: uppercase;
        }

        .muted {
            color: #333333;
        }

        .signature-space {
            height: 42px;
        }

        .signature-label {
            text-align: center;
            font-weight: 700;
        }

        .hours-head {
            background: #f4d7ae;
            text-align: center;
            font-weight: 700;
            text-transform: uppercase;
        }

        .hours-subhead {
            background: #f4d7ae;
            text-align: center;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 8px;
        }

        .text-center {
            text-align: center;
        }

        .blank-row td {
            height: 17px;
        }
    </style>
</head>
<body>
    <table class="sheet">
        <colgroup>
            <col style="width: 5.7%;">
            <col style="width: 4.5%;">
            <col style="width: 4.5%;">
            <col style="width: 9.2%;">
            <col style="width: 9.2%;">
            <col style="width: 5.7%;">
            <col style="width: 4.5%;">
            <col style="width: 4.5%;">
            <col style="width: 9.2%;">
            <col style="width: 9.2%;">
            <col style="width: 5.7%;">
            <col style="width: 4.5%;">
            <col style="width: 4.5%;">
            <col style="width: 9.2%;">
            <col style="width: 9.4%;">
        </colgroup>
        <tr>
            <td colspan="15" class="title-block">
                UNIVERSIDAD PRIVADA "FRANZ TAMAYO"<br>
                CARRERA INGENIERIA DE SISTEMAS<br>
                CARTILLA DE SEGUIMIENTO DE PRACTICA PROFESIONAL - GESTION ________
            </td>
        </tr>
        <tr>
            <td colspan="15" class="main-title">CONTROL DE HORAS PRACTICAS PROFESIONALES</td>
        </tr>
        <tr>
            <td colspan="5" class="section-title">DATOS DEL ESTUDIANTE</td>
            <td colspan="5" class="section-title">FIRMA DEL RESPONSABLE</td>
            <td colspan="5" class="section-title">DATOS DE LA INSTITUCION</td>
        </tr>
        <tr>
            <td colspan="5"><span class="label">Nombre completo:</span> {{ $estudiante['nombre'] }}</td>
            <td colspan="5" class="text-center"><span class="label">Cel.:</span> {{ $responsable['celular'] }}</td>
            <td colspan="5"><span class="label">Nombre de la empresa:</span> {{ $institucion['nombre'] }}</td>
        </tr>
        <tr>
            <td colspan="3"><span class="label">Codigo / documento de identidad:</span> {{ $estudiante['codigo'] }}</td>
            <td colspan="2"><span class="label">Telefono:</span> {{ $estudiante['telefono'] }}</td>
            <td colspan="5"><span class="label">Nombre del responsable:</span></td>
            <td colspan="5"><span class="label">Telefono de contacto:</span> {{ $institucion['telefono'] }}</td>
        </tr>
        <tr>
            <td colspan="5"><span class="label">Correo electronico institucional:</span></td>
            <td colspan="5" rowspan="2" class="muted">{{ $responsable['nombre'] }}</td>
            <td colspan="5" rowspan="3" class="muted" style="vertical-align: top;">
                <span class="label">Correo institucional:</span>
                <div style="margin-top: 6px;">{{ $institucion['correo'] }}</div>
            </td>
        </tr>
        <tr>
            <td colspan="5" class="muted">{{ $estudiante['correo'] }}</td>
        </tr>
        <tr>
            <td colspan="5" class="signature-space"></td>
            <td colspan="5" class="signature-space"></td>
        </tr>
        <tr>
            <td colspan="5" class="signature-label">Firma del Estudiante</td>
            <td colspan="5" class="signature-label">Firma del Responsable</td>
            <td colspan="5" class="signature-label">Firma y Sello Empresa</td>
        </tr>
        <tr>
            <th rowspan="2" class="hours-head">Fechas</th>
            <th colspan="2" class="hours-head">Horas</th>
            <th rowspan="2" class="hours-head">Carga horaria</th>
            <th rowspan="2" class="hours-head">Firma estudiante</th>
            <th rowspan="2" class="hours-head">Fechas</th>
            <th colspan="2" class="hours-head">Horas</th>
            <th rowspan="2" class="hours-head">Carga horaria</th>
            <th rowspan="2" class="hours-head">Firma estudiante</th>
            <th rowspan="2" class="hours-head">Fechas</th>
            <th colspan="2" class="hours-head">Horas</th>
            <th rowspan="2" class="hours-head">Carga horaria</th>
            <th rowspan="2" class="hours-head">Firma estudiante</th>
        </tr>
        <tr>
            <th class="hours-subhead">Ingreso</th>
            <th class="hours-subhead">Salida</th>
            <th class="hours-subhead">Ingreso</th>
            <th class="hours-subhead">Salida</th>
            <th class="hours-subhead">Ingreso</th>
            <th class="hours-subhead">Salida</th>
        </tr>
        @for ($row = 0; $row < 12; $row++)
            @php $item = $periodos[$row] ?? null; @endphp
            <tr class="{{ $item ? '' : 'blank-row' }}">
                <td class="text-center">{{ $item['fecha'] ?? '' }}</td>
                <td class="text-center">{{ $item['ingreso'] ?? '' }}</td>
                <td class="text-center">{{ $item['salida'] ?? '' }}</td>
                <td class="text-center">{{ $item['carga_horaria'] ?? '' }}</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
        @endfor
    </table>
</body>
</html>
