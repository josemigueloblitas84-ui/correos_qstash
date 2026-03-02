<h3 class="mb-4">{{ __('Actualizar Contraseña') }}</h3>

<p class="text-muted mb-4">
    {{ __('Asegúrese de que su cuenta utilice una contraseña larga y aleatoria para mantenerse segura.') }}
</p>

<form method="post" action="{{ route('password.update') }}">
    @csrf
    @method('put')

    <div class="form-group">
        <label for="update_password_current_password" class="form-label">{{ __('Contraseña Actual') }}</label>
        <div class="input-group">
        <input id="update_password_current_password" name="current_password" type="password" class="form-control @error('current_password', 'updatePassword') is-invalid @enderror" autocomplete="current-password">
        <div class="input-group-append">
            <span class="input-group-text toggle-password" data-target="update_password_current_password" style="cursor:pointer;">
                <i class="fas fa-eye"></i>
            </span>
        </div> 
        </div>
        @error('current_password', 'updatePassword')
            <div class="invalid-feedback d-block">
                {{ $message }}
            </div>
        @enderror
    </div>

    <div class="form-group">
        <label for="update_password_password" class="form-label">{{ __('Nueva Contraseña') }}</label>
        <div class="input-group">
        <input id="update_password_password" name="password" type="password" class="form-control @error('password', 'updatePassword') is-invalid @enderror" autocomplete="new-password">
        <div class="input-group-append">
            <span class="input-group-text toggle-password" data-target="update_password_password" style="cursor:pointer;">
                <i class="fas fa-eye"></i>
            </span>
        </div> 
        </div>
        @error('password', 'updatePassword')
            <div class="invalid-feedback d-block">
                {{ $message }}
            </div>
        @enderror
    </div>

    <div class="form-group">
        <label for="update_password_password_confirmation" class="form-label">{{ __('Confirmar Contraseña') }}</label>
        <div class="input-group">
        <input id="update_password_password_confirmation" name="password_confirmation" type="password" class="form-control @error('password_confirmation', 'updatePassword') is-invalid @enderror" autocomplete="new-password">
        <div class="input-group-append">
            <span class="input-group-text toggle-password" data-target="update_password_password_confirmation" style="cursor:pointer;">
                <i class="fas fa-eye"></i>
            </span>
        </div> 
        </div>
        @error('password_confirmation', 'updatePassword')
            <div class="invalid-feedback d-block">
                {{ $message }}
            </div>
        @enderror
    </div>

    <div class="form-group">
        <button type="submit" class="btn btn-primary">{{ __('Guardar') }}</button>
        
        @if (session('status') === 'password-updated')
            <div class="alert alert-success ms-2 d-inline-block">
                {{ __('Guardado exitosamente.') }}
            </div>
        @endif
    </div>
</form>
