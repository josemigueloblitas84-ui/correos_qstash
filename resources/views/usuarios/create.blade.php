@extends('plantilla.app')

@section('title', 'Usuarios / Crear')

@section('content')

    <div class="content-header">
        <div class="container-fluid">
            <div class="d-flex justify-content-between">
                <h1>Usuarios / Crear</h1>
                <a href="{{ route('usuarios.index') }}" class="btn btn-primary">
                    Volver
                </a>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <!-- left column -->
                <div class="col-md-6">
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
                                <div class="form-group">
                                    <label for="nombreUsuario">Nombre:</label>
                                    <input value="{{ old('nombreUsuario') }}" type="text" class="form-control"
                                        id="nombreUsuario" placeholder="Ingrese el nombre del usuario" name="nombreUsuario">
                                    @error('nombreUsuario')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="correoUsuario">Correo:</label>
                                    <input value="{{ old('correoUsuario') }}" type="email" class="form-control"
                                        id="correoUsuario" placeholder="Ingrese el correo del usuario" name="correoUsuario">
                                    @error('correoUsuario')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="contrasenaUsuario">Contraseña:</label>
                                    <input value="{{ old('contrasenaUsuario') }}" type="password" class="form-control"
                                        id="contrasenaUsuario" placeholder="Ingrese la contraseña del usuario"
                                        name="contrasenaUsuario">
                                    @error('contrasenaUsuario')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="confirmar_contrasenaUsuario">Confirmar Contraseña:</label>
                                    <input value="{{ old('confirmar_contrasenaUsuario') }}" type="password" class="form-control"
                                        id="confirmar_contrasenaUsuario" placeholder="Confirme la contraseña del usuario"
                                        name="confirmar_contrasenaUsuario">
                                    @error('confirmar_contrasenaUsuario')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                                <div class="form-group mb-3">
                                    <label class="d-block">Roles</label>

                                    @if ($roles->isNotEmpty())
                                        <div class="row">
                                            @foreach ($roles as $role)
                                                <div class="col-12 col-md-6 mb-2">
                                                    <div class="form-check">
                                                        <input type="checkbox" class="form-check-input"
                                                            id="role-{{ $role->id }}" name="role[]"
                                                            value="{{ $role->name }}">
                                                        <label class="form-check-label" for="role-{{ $role->id }}">
                                                            {{ $role->name }}
                                                        </label>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>




                                {{-- <div class="form-group">
                                    <label for="exampleInputPassword1">Password</label>
                                    <input type="password" class="form-control" id="exampleInputPassword1"
                                        placeholder="Password">
                                </div>
                                <div class="form-group">
                                    <label for="exampleInputFile">File input</label>
                                    <div class="input-group">
                                        <div class="custom-file">
                                            <input type="file" class="custom-file-input" id="exampleInputFile">
                                            <label class="custom-file-label" for="exampleInputFile">Choose file</label>
                                        </div>
                                        <div class="input-group-append">
                                            <span class="input-group-text">Upload</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="exampleCheck1">
                                    <label class="form-check-label" for="exampleCheck1">Check me out</label>
                                </div>
                            </div> --}}
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
    </section>

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
            <div class="form-group">
                <label for="exampleInputEmail1">Email address</label>
                <input type="email" class="form-control" id="exampleInputEmail1" placeholder="Enter email">
            </div>
            <div class="form-group">
                <label for="exampleInputPassword1">Password</label>
                <input type="password" class="form-control" id="exampleInputPassword1" placeholder="Password">
            </div>
            <div class="form-group">
                <label for="exampleInputFile">File input</label>
                <div class="input-group">
                    <div class="custom-file">
                        <input type="file" class="custom-file-input" id="exampleInputFile">
                        <label class="custom-file-label" for="exampleInputFile">Choose file</label>
                    </div>
                    <div class="input-group-append">
                        <span class="input-group-text">Upload</span>
                    </div>
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
