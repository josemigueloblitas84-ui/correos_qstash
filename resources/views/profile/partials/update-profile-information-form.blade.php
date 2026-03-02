<h3 class="mb-4">{{ __('Información del Perfil') }}</h3>

<p class="text-muted mb-4">
    {{ __("Actualice la información del perfil y la dirección de correo electrónico de su cuenta.") }}
</p>

<form method="post" action="{{ route('profile.update') }}">
    @csrf
    @method('patch')

    <div class="form-group">
        <label for="name">{{ __('Nombre') }}</label>
        <input 
            id="name"
            name="name"
            type="text"
            class="form-control @error('name') is-invalid @enderror"
            value="{{ old('name', $user->name) }}"
            required
            autofocus
        >

        @error('name')
            <div class="invalid-feedback d-block">
                {{ $message }}
            </div>
        @enderror
    </div>

    <div class="form-group">
        <label for="email">{{ __('Correo Electrónico') }}</label>
        <input 
            id="email"
            name="email"
            type="email"
            class="form-control @error('email') is-invalid @enderror"
            value="{{ old('email', $user->email) }}"
            required
        >

        @error('email')
            <div class="invalid-feedback d-block">
                {{ $message }}
            </div>
        @enderror
    </div>

    <div class="form-group">
        <button type="submit" class="btn btn-primary">
            {{ __('Guardar') }}
        </button>

        @if (session('status') === 'profile-updated')
            <div class="alert alert-success d-inline-block ml-2">
                {{ __('Guardado exitosamente.') }}
            </div>
        @endif
    </div>
</form>