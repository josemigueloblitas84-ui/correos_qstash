<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Support\Facades\Storage;

class FormularioMail extends Mailable
{
    use Queueable, SerializesModels;
    public $data;

    /**
     * Create a new message instance.
     */
    public function __construct($data)
    {
        $this->data =$data;
    }

        public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Nuevo formulario recibido',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'correo',
        );
    }
    /**
     * Get the message envelope.
     */

    /**
     * Get the attachments for the message.
     *
     */

    // Construye la lista de adjuntos para el correo: toma los datos (path/name/mime),
    // verifica que el archivo exista en Storage local y lo convierte en objetos Attachment.
    public function attachments(): array 
    {
        $files = $this->data['adjuntos'] ?? [];

        return collect($files)
            ->filter(fn ($f) => isset($f['path']) && Storage::disk('local')->exists($f['path']))
            ->map(fn ($f) => Attachment::fromStorageDisk('local', $f['path'])
                ->as($f['name'] ?? basename($f['path']))
                ->withMime($f['mime'] ?? 'application/octet-stream'))
            ->all();
    }
}
