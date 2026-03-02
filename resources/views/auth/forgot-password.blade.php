@extends('layouts.adminLTE_guest')

@section('content')

<p class="login-box-msg">
    ¿Olvidaste tu contraseña? Introduce tu correo electrónico y te enviaremos un enlace para restablecerla.
</p>

@if (session('status'))
    <div class="alert alert-success">
        {{ session('status') }}
    </div>
@endif

<form method="POST" action="{{ route('password.email') }}">
    @csrf

    <div class="input-group mb-3">
        <input type="email"
               name="email"
               value="{{ old('email') }}"
               class="form-control @error('email') is-invalid @enderror"
               placeholder="Email"
               required
               autofocus>

        <div class="input-group-append">
            <div class="input-group-text">
                <span class="fas fa-envelope"></span>
            </div>
        </div>

        @error('email')
            <span class="invalid-feedback d-block" role="alert">
                <strong>{{ $message }}</strong>
            </span>
        @enderror
    </div>

    <div class="row">
        <div class="col-12">
            <button type="submit" class="btn btn-primary btn-block">      
                Enlace para restablecer contraseña de correo electrónico
            </button>
        </div>
    </div>

</form>

@endsection