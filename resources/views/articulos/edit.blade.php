@extends('plantilla.app')

@section('title', 'Articulos / Editar')

@section('content')
    <div class="app-content-header my-3">
        <div class="container-fluid d-flex justify-content-between align-items-center">
            <div>
                <h2 class="mb-0">Articulos / Editar</h2>
            </div>
            <a href="{{ route('articulos.index') }}" class="btn btn-outline-secondary">
                Volver al listado
            </a>
        </div>
    </div>

    <div class="app-content">
        <div class="container-fluid">
            @include('mensajes')

            @include('articulos.partials.form-v2', [
                'action' => route('articulos.update', $articulo),
                'method' => 'PUT',
                'articulo' => $articulo,
            ])
        </div>
    </div>
@endsection
