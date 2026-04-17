@extends('plantilla.app')

@section('title', 'Agenda / Editar')

@section('content')
    <div class="app-content-header my-3">
        <div class="container-fluid d-flex justify-content-between align-items-center">
            <div>
                <h2 class="mb-0">Agenda / Editar</h2>
            </div>
            <a href="{{ route('agenda.create') }}" class="btn btn-outline-secondary">
                Volver a agenda
            </a>
        </div>
    </div>

    <div class="app-content">
        <div class="container-fluid">
            @include('mensajes')

            @include('agenda.partials.form-v2', [
                'action' => route('agenda.update', $agenda),
                'method' => 'PUT',
                'agenda' => $agenda,
            ])
        </div>
    </div>
@endsection
