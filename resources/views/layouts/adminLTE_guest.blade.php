<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name') }}</title>
    <link rel="stylesheet" href="{{ asset('assets/plugins/fontawesome-free/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/adminlte.min.css') }}">
    @stack('styles')
</head>

<body class="@yield('body_class', 'hold-transition login-page')">

@hasSection('full_width_auth')
    @yield('content')
@else
    <div class="login-box">
        <div class="card">
            @yield('content')
        </div>
    </div>
@endif

<script src="{{ asset('assets/plugins/jquery/jquery.min.js') }}"></script>
<script src="{{ asset('assets/plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('assets/js/adminlte.min.js') }}"></script>

<script>
document.addEventListener("click", function (e) {

    const toggle = e.target.closest(".toggle-password");
    if (!toggle) return;

    const inputGroup = toggle.closest(".input-group");
    const input = inputGroup.querySelector(".password-field");
    const icon = toggle.querySelector("i");

    if (input.type === "password") {
        input.type = "text";
        icon.classList.remove("fa-eye");
        icon.classList.add("fa-eye-slash");
    } else {
        input.type = "password";
        icon.classList.remove("fa-eye-slash");
        icon.classList.add("fa-eye");
    }

});
</script>
@stack('scripts')

</body>
</html>
