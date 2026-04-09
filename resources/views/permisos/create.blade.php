@extends('plantilla.app')

@section('title', 'Permisos / Crear')

@section('content')

    <div class="app-content-header">
        <div class="container-fluid">
            <div class="d-flex justify-content-between">
                <h1>Permisos / Crear</h1>
                {{--<a href="{{ route('permisos.index') }}" class="btn btn-primary">
                    Volver
                </a>--}}
            </div>
        </div>
    </div>

    <div class="app-content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-body">

                    <form action="{{ route('permisos.store') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label for="name" class="form-label fw-bold">Nombre:</label>
                            <input 
                                type="text"
                                name="name"
                                id="name"
                                value="{{ old('name') }}"
                                class="form-control"
                                placeholder="Ingrese el nombre">

                            @error('name')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-success">
                            Enviar
                        </button>

                    </form>

                </div>
            </div>
        </div>
    </div>

@endsection