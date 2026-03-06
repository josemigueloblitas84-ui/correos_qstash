@extends('layouts.adminLTE_guest')

@section('content')
    <div class="register-box">
        <div class="card card-outline card-primary">
            <div class="card-body">
                <p class="login-box-msg">Registrar Nuevo Usuario</p>

                <form method="POST" action="{{ route('register') }}">
                    @csrf

                    <div class="input-group mb-3">
                        <input
                            type="text"
                            name="name"
                            class="form-control @error('name') is-invalid @enderror"
                            placeholder="Nombre completo"
                            value="{{ old('name') }}"
                            required
                            autofocus
                            autocomplete="name"
                        >
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-user"></span>
                            </div>
                        </div>
                        @error('name')
                            <span class="invalid-feedback d-block">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="input-group mb-3">
                        <input
                            type="email"
                            name="email"
                            class="form-control @error('email') is-invalid @enderror"
                            placeholder="Correo"
                            value="{{ old('email') }}"
                            required
                            autocomplete="username"
                        >
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-envelope"></span>
                            </div>
                        </div>
                        @error('email')
                            <span class="invalid-feedback d-block">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="input-group mb-3">
                        <input
                            type="password"
                            id="password"
                            name="password"
                            class="form-control password-field @error('password') is-invalid @enderror"
                            placeholder="Contraseña"
                            required
                            autocomplete="new-password"
                        >
                        <div class="input-group-append">
                            <div class="input-group-text toggle-password" data-target="password" style="cursor:pointer;">
                                <span class="fas fa-eye"></span>
                            </div>
                        </div>
                        @error('password')
                            <span class="invalid-feedback d-block">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="input-group mb-3">
                        <input
                            type="password"
                            id="password_confirmation"
                            name="password_confirmation"
                            class="form-control password-field @error('password_confirmation') is-invalid @enderror"
                            placeholder="Confirmar contraseña"
                            required
                            autocomplete="new-password"
                        >
                        <div class="input-group-append">
                            <div class="input-group-text toggle-password" data-target="password_confirmation" style="cursor:pointer;">
                                <span class="fas fa-eye"></span>
                            </div>
                        </div>
                        @error('password_confirmation')
                            <span class="invalid-feedback d-block">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-8">
                            <a href="{{ route('login') }}" class="text-center">
                                Ya tengo una cuenta
                            </a>
                        </div>

                        <div class="col-4">
                            <button type="submit" class="btn btn-primary btn-block">
                                Registrar
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
