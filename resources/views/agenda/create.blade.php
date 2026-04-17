@extends('plantilla.app')

@section('title', 'Crear Agenda')

@section('content')
    <div class="app-content-header my-3">
        <div class="container-fluid d-flex justify-content-between align-items-center">
            <div>
                <h2>Crear Agenda</h2>
            </div>
            {{-- <a href="{{ route('agenda.create') }}" class="btn btn-outline-secondary">
                Volver al listado
            </a> --}}
        </div>
    </div>

    <div class="app-content">
        <div class="container-fluid">
            @include('mensajes')

            @include('agenda.partials.form-v2', [
                'action' => route('agenda.store'),
                'method' => 'POST',
            ])
        </div>
    </div>
@endsection
