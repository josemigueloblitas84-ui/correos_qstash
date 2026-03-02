<div class="card card-danger">
    <div class="card-header">
        <h3 class="card-title">{{ __('Eliminar Cuenta') }}</h3>
    </div>
    <div class="card-body">
        <p>
            {{ __('Una vez que se elimine su cuenta, todos sus recursos y datos se eliminarán permanentemente. Antes de eliminar su cuenta, descargue cualquier dato o información que desee conservar.') }}
        </p>

        <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#confirmDeleteModal">
            {{ __('Eliminar Cuenta') }}
        </button>
    </div>
</div>

<!-- Modal de Confirmación -->
<div class="modal fade" id="confirmDeleteModal" tabindex="-1" role="dialog" aria-labelledby="confirmDeleteLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="confirmDeleteLabel">{{ __('¿Está seguro?') }}</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="post" action="{{ route('profile.destroy') }}">
                <div class="modal-body">
                    @csrf
                    @method('delete')

                    <h5>{{ __('¿Está seguro de que desea eliminar su cuenta?') }}</h5>

                    <p class="text-muted">
                        {{ __('Una vez que se elimine su cuenta, todos sus recursos y datos se eliminarán permanentemente. Por favor, ingrese su contraseña para confirmar que desea eliminar permanentemente su cuenta.') }}
                    </p>

                    <div class="form-group">
                        <label for="password" class="form-label">{{ __('Contraseña') }}</label>
                        <input 
                            id="password" 
                            name="password" 
                            type="password" 
                            class="form-control @error('password', 'userDeletion') is-invalid @enderror" 
                            placeholder="{{ __('Ingrese su contraseña') }}"
                            required
                        >
                        @error('password', 'userDeletion')
                            <div class="invalid-feedback d-block">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        {{ __('Cancelar') }}
                    </button>
                    <button type="submit" class="btn btn-danger">
                        {{ __('Eliminar Cuenta') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
