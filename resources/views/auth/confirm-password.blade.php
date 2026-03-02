@extends('layouts.adminLTE_guest')

@section('content')

<p class="login-box-msg">
    Esta es una zona segura de la aplicación. Confirme su contraseña antes de continuar.
</p>

<form method="POST" action="{{ route('password.confirm') }}">
    @csrf

    <x-password-input 
        name="password" 
        placeholder="Contraseña"
        required 
        autocomplete="current-password"
    />

    <div class="row">
        <div class="col-12">
            <button type="submit" class="btn btn-primary btn-block">
                Confirmar
            </button>
        </div>
    </div>

</form>

@endsection