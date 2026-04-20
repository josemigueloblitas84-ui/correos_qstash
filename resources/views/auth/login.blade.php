@extends('layouts.adminLTE_guest')

@section('full_width_auth', true)
@section('body_class', 'hold-transition auth-split-page')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/auth/login-split.css') }}">
@endpush

@section('content')
    <main class="container-fluid px-0">
        <div class="row g-0 auth-split">
            <div class="col-lg-6 auth-split__brand">
                <div class="auth-split__brand-shell">
                    <div class="auth-split__brand-panel">
                        <img
                            src="{{ asset('assets/img/logoFundacionTrans.png') }}"
                            alt="Logo institucional 1"
                            class="auth-split__logo auth-split__logo--top">
                        <span class="auth-split__logo-divider" aria-hidden="true"></span>
                        <img
                            src="{{ asset('assets/img/unifranz_loguito.png') }}"
                            alt="Logo institucional 2"
                            class="auth-split__logo auth-split__logo--bottom">
                    </div>
                </div>
            </div>

            <div class="col-lg-6 auth-split__form">
                <div class="auth-split__card">
                    <div class="auth-split__card-header">
                        <h1 class="auth-split__title">Iniciar sesion</h1>
                    </div>

                    <div class="auth-split__card-body">
                        <p class="auth-split__subtitle">Ingresa tus credenciales para continuar</p>

                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <strong>Error.</strong> Revisa los errores debajo.
                            </div>
                        @endif

                        <div class="auth-split__form-shell">
                            <form method="POST" action="{{ route('login') }}">
                                @csrf

                                <div class="auth-split__field">
                                    <label for="email" class="auth-split__label">Correo electronico</label>
                                    <div class="input-group mb-3">
                                        <input
                                            type="email"
                                            id="email"
                                            name="email"
                                            value="{{ old('email') }}"
                                            class="form-control @error('email') is-invalid @enderror"
                                            placeholder="Email"
                                            required
                                            autofocus
                                            autocomplete="username">

                                        <div class="input-group-append">
                                            <div class="input-group-text">
                                                <span class="fas fa-envelope"></span>
                                            </div>
                                        </div>

                                        @error('email')
                                            <span class="invalid-feedback d-block">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="auth-split__field">
                                    <label for="password" class="auth-split__label">Contrasena</label>
                                    <x-password-input
                                        name="password"
                                        id="password"
                                        placeholder="Contrasena"
                                        required
                                        autocomplete="current-password"
                                    />
                                </div>

                                <div class="auth-split__actions">
                                    <button type="submit" class="btn btn-primary auth-split__submit">
                                        <span>Iniciar sesion</span>
                                        <i class="fas fa-arrow-right"></i>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
@endsection
