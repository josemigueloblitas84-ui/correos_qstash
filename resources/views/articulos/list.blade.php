@extends('plantilla.app')

@section('title', 'Articulos')

@section('content')
    <div class="app-content-header my-3">
        <div class="container-fluid d-flex justify-content-between align-items-center">
            <div>
                <h2 class="mb-0">Articulos</h2>
            </div>

            <a href="{{ route('articulos.create') }}" class="btn btn-primary">
                Crear articulo
            </a>
        </div>
    </div>

    <div class="app-content">
        <div class="container-fluid">
            @include('mensajes')

            <div class="card">
                <div class="card-body table-responsive">
                    <table id="example1" class="table table-bordered table-striped align-middle">
                        <thead class="table-light">
                            <tr>
                                <th width="80">#</th>
                                <th>Codigo</th>
                                <th>Nombre</th>
                                <th>Categoria</th>
                                <th>Estado</th>
                                <th>Precio</th>
                                <th>Stock</th>
                                <th width="220">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($articulos as $articulo)
                                <tr>
                                    <td>{{ $articulo->id }}</td>
                                    <td><span class="badge text-bg-light border">{{ $articulo->codigo }}</span></td>
                                    <td>
                                        <div class="fw-semibold">{{ $articulo->nombre }}</div>
                                        @if ($articulo->marca)
                                            <small class="text-muted">Marca: {{ $articulo->marca }}</small>
                                        @endif
                                    </td>
                                    <td>{{ $articulo->categoria_texto }}</td>
                                    <td>
                                        <span class="badge {{ $articulo->estado === 'activo' ? 'text-bg-success' : 'text-bg-secondary' }}">
                                            {{ ucfirst($articulo->estado) }}
                                        </span>
                                    </td>
                                    <td>Bs {{ number_format((float) $articulo->precio, 2) }}</td>
                                    <td>{{ $articulo->stock }}</td>
                                    <td>
                                        <div class="d-flex gap-2 flex-wrap">
                                            {{--<a href="{{ route('articulos.edit', $articulo) }}" class="btn btn-warning btn-sm">--}}
                                            <a href="{{ route('articulos.edit', $articulo->id) }}" class="btn btn-warning btn-sm">
                                                Editar
                                            </a>

                                            {{--<form action="{{ route('articulos.destroy', $articulo) }}" method="POST" class="d-inline">--}}
                                            <form action="{{ route('articulos.destroy', $articulo->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button
                                                    type="submit"
                                                    class="btn btn-danger btn-sm"
                                                    onclick="return confirm('�Desea eliminar el articulo?')">
                                                    Eliminar
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
