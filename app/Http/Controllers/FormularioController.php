<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use App\Mail\FormularioMail;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Support\Facades\Log;
use App\Services\Support\ActivityLogger;

class FormularioController extends Controller
{
 public function index()
 {
     return view('formulario');
 }
 
public function store(Request $request)
{

    // validaciones del lado del backend
    $validated = $request->validate([
        'nombre' => ['required', 'string', 'max:120'],
        'email' => ['required', 'email', 'max:190'],
        'mensaje' => ['required', 'string', 'max:5000'],
        'adjuntos' => ['nullable', 'array', 'max:5'],
        'adjuntos.*' => ['file', 'mimes:pdf,jpg,jpeg,png,doc,docx,xls,xlsx,txt', 'max:5120'],
    ]);

    // Array para ir guardando los adjuntos y devolver la informacion
    $adjuntos = [];

    if ($request->hasFile('adjuntos')) {
        foreach ($request->file('adjuntos') as $file) {
            $path = $file->store('correo_adjuntos', 'local');

            $adjuntos[] = [
                'path' => $path,
                'name' => $file->getClientOriginalName(),
                'mime' => $file->getMimeType(),
            ];
        }
    }

    $endpoint = env('QSTASH_BASE_URL')
        . '/v2/publish/'
        . env('QSTASH_ENDPOINT');

    $response = Http::withHeaders([
        'Authorization' => 'Bearer ' . env('QSTASH_TOKEN'),
        'Upstash-Delay' => '300s',
    ])->post($endpoint, [
        'nombre' => $validated['nombre'],
        'email' => $validated['email'],
        'mensaje' => $validated['mensaje'],
        'adjuntos' => $adjuntos,
    ]);

    ActivityLogger::log(
        'Formulario enviado a QStash',
        [
            'formulario' => [
                'nombre' => $validated['nombre'],
                'email' => $validated['email'],
                'adjuntos_count' => count($adjuntos),
            ],
            'qstash' => [
                'status' => $response->status(),
            ],
        ],
        logName: 'formularios',
        event: 'submitted'
    );


    return response()->json([
        'status' => $response->status(),
        'body' => $response->body(),
    ]);
}



public function enviarEmail(Request $request)
{
    try {
    
        $datos = $request->json()->all();

        Mail::to('oblitasjosemiguel4@gmail.com')
            ->send(new FormularioMail($datos));

        ActivityLogger::log(
            'Correo del formulario procesado por QStash',
            [
                'formulario' => [
                    'nombre' => $datos['nombre'] ?? null,
                    'email' => $datos['email'] ?? null,
                    'adjuntos_count' => count($datos['adjuntos'] ?? []),
                ],
            ],
            logName: 'formularios',
            event: 'processed'
        );

        return response()->json([
            'status' => 'Correo enviado correctamente'
        ], 200);

    } catch (\Throwable $e) {

        return response()->json([
            'error' => $e->getMessage()
        ], 500);
    }
}

}
