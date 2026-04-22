@extends('plantilla.app')

@section('title', 'Permisos Especiales')

@section('content')

    <div class="app-content-header">
        <div class="container-fluid">
            <h1>Permisos especiales de: {{ $usuario->name }}</h1>
        </div>
    </div>

    <div class="app-content">
        <div class="container-fluid">
            @include('mensajes')

            <div class="card">
                <div class="card-body">
                    <form action="{{ route('usuarios.permisos.update', $encryptedId) }}" method="POST">
                        @csrf

                        <p class="text-body-secondary">
                            Marca permisos directos para este usuario. Los permisos de rol siguen funcionando aparte.
                        </p>

                        <div class="row">
                            @foreach($permisos as $permiso)
                                @php
                                    $isDirect = in_array($permiso->name, $directPermissions, true);
                                    $isViaRole = in_array($permiso->name, $rolePermissions, true);
                                @endphp

                                <div class="col-md-4 mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input"
                                               type="checkbox"
                                               name="permisos[]"
                                               id="permiso-{{ $permiso->id }}"
                                               value="{{ $permiso->name }}"
                                               {{ $isDirect ? 'checked' : '' }}
                                               style="transform: scale(1.2);">

                                        <label class="form-check-label fw-bold" for="permiso-{{ $permiso->id }}">
                                            {{ $permiso->name }}
                                        </label>

                                        @if($isViaRole)
                                            <span class="badge text-bg-secondary ms-2">vía rol</span>
                                        @endif

                                        @if($isDirect)
                                            <span class="badge text-bg-info ms-2">directo</span>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <button type="submit" class="btn btn-primary">Guardar permisos especiales</button>
                        <a href="{{ route('usuarios.index') }}" class="btn btn-secondary">Volver</a>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection
