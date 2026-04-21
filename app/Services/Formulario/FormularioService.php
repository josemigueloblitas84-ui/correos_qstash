<?php

namespace App\Services\Formulario;

use App\Mail\FormularioMail;
use App\Services\Support\ActivityLogger;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;

class FormularioService
{
    public function submit(array $validated, array $uploadedFiles = []): array
    {
        $adjuntos = [];

        foreach ($uploadedFiles as $file) {
            $path = $file->store('correo_adjuntos', 'local');

            $adjuntos[] = [
                'path' => $path,
                'name' => $file->getClientOriginalName(),
                'mime' => $file->getMimeType(),
            ];
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
                'attributes' => [
                    'nombre' => $validated['nombre'],
                    'email' => $validated['email'],
                    'adjuntos_count' => count($adjuntos),
                    'qstash_status' => $response->status(),
                ],
            ],
            logName: 'formularios',
            event: 'submitted'
        );

        return [
            'status' => $response->status(),
            'body' => $response->body(),
        ];
    }

    public function processEmailPayload(array $data): void
    {
        Mail::to('oblitasjosemiguel4@gmail.com')
            ->send(new FormularioMail($data));

        ActivityLogger::log(
            'Correo del formulario procesado por QStash',
            [
                'attributes' => [
                    'nombre' => $data['nombre'] ?? null,
                    'email' => $data['email'] ?? null,
                    'adjuntos_count' => count($data['adjuntos'] ?? []),
                ],
            ],
            logName: 'formularios',
            event: 'processed'
        );
    }
}
