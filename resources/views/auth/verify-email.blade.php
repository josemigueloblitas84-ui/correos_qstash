@extends('layouts.adminLTE_guest')

@section('content')

<p class="login-box-msg">
    ¡Gracias por iniciar sesión! Antes de comenzar, 
    ¿podría verificar su dirección de correo electrónico haciendo 
    clic en el enlace que le acabamos de enviar por correo electrónico? 
    Si no recibió el correo electrónico, con gusto le enviaremos otro.    
</p>

@if (session('status') == 'verification-link-sent')
    <div class="alert alert-success">
        Se ha enviado un nuevo enlace de verificación a su dirección de correo electrónico.
    </div>
@endif

<form method="POST" action="{{ route('verification.send') }}">
    @csrf

    <button type="submit" class="btn btn-primary btn-block">
        REENVIAR CORREO DE VERIFICACIÓN
    </button>
</form>

<form method="POST" action="{{ route('logout') }}" class="mt-3">
    @csrf

    <button type="submit" class="btn btn-secondary btn-block">
        Finalizar Sesion
    </button>
</form>

@endsection