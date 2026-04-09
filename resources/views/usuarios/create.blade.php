@extends('plantilla.app')

@section('title', 'Usuarios / Crear')

@section('content')

    <div class="app-content-header">
        <div class="container-fluid">
            <div class="d-flex justify-content-between">
                <h1>Usuarios / Crear</h1>
                {{--<a href="{{ route('usuarios.index') }}" class="btn btn-primary">
                    Volver
                </a>--}}
            </div>
        </div>
    </div>

    <div class="app-content">
        <div class="container-fluid">
            <div class="row">
                <!-- left column -->
                <div class="col-md-12">
                    <!-- general form elements -->
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title">Crear Usuario</h3>
                        </div>
                        <!-- /.card-header -->
                        <!-- form start -->
                        <form action="{{ route('usuarios.store') }}" method="POST">
                            @csrf
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="nombreUsuario" class="form-label fw-bold">Nombre:</label>
                                    <input value="{{ old('nombreUsuario') }}" type="text" class="form-control"
                                        id="nombreUsuario" placeholder="Ingrese el nombre del usuario" name="nombreUsuario">
                                    @error('nombreUsuario')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="correoUsuario" class="form-label fw-bold">Correo:</label>
                                    <input value="{{ old('correoUsuario') }}" type="email" class="form-control"
                                        id="correoUsuario" placeholder="Ingrese el correo del usuario" name="correoUsuario">
                                    @error('correoUsuario')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="contrasenaUsuario" class="form-label fw-bold">Contraseña:</label>
                                    <input value="{{ old('contrasenaUsuario') }}" type="password" class="form-control"
                                        id="contrasenaUsuario" placeholder="Ingrese la contraseña del usuario"
                                        name="contrasenaUsuario">
                                    @error('contrasenaUsuario')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="confirmar_contrasenaUsuario" class="form-label fw-bold">Confirmar Contraseña:</label>
                                    <input value="{{ old('confirmar_contrasenaUsuario') }}" type="password" class="form-control"
                                        id="confirmar_contrasenaUsuario" placeholder="Confirme la contraseña del usuario"
                                        name="confirmar_contrasenaUsuario">
                                    @error('confirmar_contrasenaUsuario')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="d-block form-label fw-bold">Roles</label>

                                    @if ($roles->isNotEmpty())
                                        <div class="row">
                                            @foreach ($roles as $role)
                                                <div class="col-12 col-md-6 mb-2">
                                                    <div class="form-check form-switch">
                                                        <input type="checkbox" class="form-check-input"
                                                            id="role-{{ $role->id }}" name="role[]"
                                                            value="{{ $role->name }}"
                                                            style="transform: scale(1.2);">
                                                        <label class="form-check-label fw-bold" for="role-{{ $role->id }}">
                                                            {{ $role->name }}
                                                        </label>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>

                                {{--
                                <div class="mb-3">
                                    <label for="exampleInputPassword1" class="form-label">Password</label>
                                    <input type="password" class="form-control" id="exampleInputPassword1"
                                        placeholder="Password">
                                </div>
                                <div class="mb-3">
                                    <label for="exampleInputFile" class="form-label">File input</label>
                                    <div class="input-group">
                                        <input type="file" class="form-control" id="exampleInputFile">
                                    </div>
                                </div>
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="exampleCheck1">
                                    <label class="form-check-label" for="exampleCheck1">Check me out</label>
                                </div>
                                --}}
                            </div>
                            <!-- /.card-body -->

                            <div class="card-footer">
                                <button type="submit" class="btn btn-primary">Crear Usuario</button>
                            </div>
                        </form>
                    </div>
                    <!-- /.card -->
                </div>
            </div>
        </div>
    </div>

@endsection

{{-- <!-- general form elements -->
<div class="card card-primary">
    <div class="card-header">
        <h3 class="card-title">Quick Example</h3>
    </div>
    <!-- /.card-header -->
    <!-- form start -->
    <form>
        <div class="card-body">
            <div class="mb-3">
                <label for="exampleInputEmail1" class="form-label">Email address</label>
                <input type="email" class="form-control" id="exampleInputEmail1" placeholder="Enter email">
            </div>
            <div class="mb-3">
                <label for="exampleInputPassword1" class="form-label">Password</label>
                <input type="password" class="form-control" id="exampleInputPassword1" placeholder="Password">
            </div>
            <div class="mb-3">
                <label for="exampleInputFile" class="form-label">File input</label>
                <div class="input-group">
                    <input type="file" class="form-control" id="exampleInputFile">
                </div>
            </div>
            <div class="form-check">
                <input type="checkbox" class="form-check-input" id="exampleCheck1">
                <label class="form-check-label" for="exampleCheck1">Check me out</label>
            </div>
        </div>
        <!-- /.card-body -->

        <div class="card-footer">
            <button type="submit" class="btn btn-primary">Submit</button>
        </div>
    </form>
</div>
<!-- /.card --> --}}