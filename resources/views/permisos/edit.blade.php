@extends('plantilla.app')

@section('title', 'Permisos / Editar')

@section('content')

<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Permisos / Editar</h1>
            </div>
            <div class="col-sm-6 text-right">
                <a href="{{ route('permisos.index') }}" 
                   class="btn btn-primary">
                    Volver
                </a>
            </div>
        </div>
    </div>
</section>

<section class="content">
    <div class="container-fluid">
        <div class="card">
            <div class="card-body">

                <form action="{{ route('permisos.update', $permiso->id) }}" method="POST">
                    @csrf

                    <div class="form-group">
                        <label for="name">Nombre:</label>

                        <input 
                            value="{{ old('name', $permiso->name) }}" 
                            name="name" 
                            id="name" 
                            type="text" 
                            class="form-control"
                            placeholder="Ingrese el nombre"
                        >

                        @error('name')
                            <span class="text-danger">
                                {{ $message }}
                            </span>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-success">
                        Actualizar Permiso
                    </button>

                </form>

            </div>
        </div>
    </div>
</section>

@endsection