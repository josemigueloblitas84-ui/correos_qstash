@extends('plantilla.app')

@section('title', 'Permisos / Crear')

@section('content')

<div class="content-header">
    <div class="container-fluid">
        <div class="d-flex justify-content-between">
            <h1>Permisos / Crear</h1>
            <a href="{{ route('permisos.index') }}" class="btn btn-primary">
                Volver
            </a>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="card">
            <div class="card-body">

                <form action="{{ route('permisos.store') }}" method="POST">
                    @csrf

                    <div class="form-group">
                        <label>Nombre:</label>
                        <input type="text"
                               name="name"
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
</section>

@endsection