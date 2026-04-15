<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;

class DashboardController extends Controller
{
    public function controlHorasPreview()
    {
        $previewData = [
            'estudiante' => [
                'nombre' => 'Marco Antonio Fuentes',
                'codigo' => 'SIS11107745',
                'telefono' => '69807494',
                'correo' => 'marco.fuentes@fundacionunifranz.org',
            ],
            'responsable' => [
                'nombre' => 'Victor Velasquez',
                'celular' => '',
            ],
            'institucion' => [
                'nombre' => 'FUNDACION UNIFRANZ',
                'telefono' => '',
            ],
            'periodos' => [
                [
                    'fecha' => '01/04/2026',
                    'ingreso' => '09:00',
                    'salida' => '17:00',
                    'carga_horaria' => '08:00',
                ],
            ],
        ];

        return Pdf::loadView('dashboard.partials.control-horas-preview-document', $previewData)
            ->setPaper('letter', 'landscape')
            ->stream('control-horas.pdf');
    }
}
