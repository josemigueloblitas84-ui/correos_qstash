@extends('layouts.adminLTE_guest')

@section('content')

<div class="card card-outline card-primary">
    <div class="card-header text-center">
        <span class="h4">Inicia Sesión</span>
    </div>

    <div class="card-body">
        <p class="login-box-msg">Ingresa tus credenciales para continuar</p>

        @if ($errors->any())
            <div class="alert alert-danger">
                <strong>Error!</strong> Revisa los errores debajo.
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <!-- Email -->
            <div class="input-group mb-3">
                <input type="email"
                       name="email"
                       value="{{ old('email') }}"
                       class="form-control @error('email') is-invalid @enderror"
                       placeholder="Email"
                       required autofocus autocomplete="username">

                <div class="input-group-append">
                    <div class="input-group-text">
                        <span class="fas fa-envelope"></span>
                    </div>
                </div>

                @error('email')
                    <span class="invalid-feedback d-block">{{ $message }}</span>
                @enderror
            </div>

            <!-- Password -->
            <x-password-input
              name="password"
              placeholder="Contraseña"
              required
              autocomplete="current-password"
            />

            <!-- Remember -->
            <div class="row">
                <div class="col-8">
                    <div class="icheck-primary">
                        <input type="checkbox" id="remember" name="remember">
                        <label for="remember">
                            Recuérdame
                        </label>
                    </div>
                </div>

                <div class="col-4">
                    <button type="submit" class="btn btn-primary btn-block">
                        Iniciar Sesión
                    </button>
                </div>
            </div>
        </form>

        @if (Route::has('password.request'))
            <p class="mt-3 mb-1">
                <a href="{{ route('password.request') }}">
                    ¿Olvidaste tu contraseña?
                </a>
            </p>
        @endif

        @if (Route::has('register'))
            <p class="mb-0">
                <a href="{{ route('register') }}">
                    Crear una nueva cuenta
                </a>
            </p>
        @endif

    </div>
</div>

@endsection
@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r134/three.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/vanta@latest/dist/vanta.waves.min.js"></script>

<script>
VANTA.WAVES({
  el: "body",
  mouseControls: true,
  touchControls: true,
  gyroControls: false,
  minHeight: 200.00,
  minWidth: 200.00,
  scale: 1.00,
  scaleMobile: 1.00
})
</script>
@endpush
