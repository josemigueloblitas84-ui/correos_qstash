@extends('plantilla.app')

@section('title', 'Permisos Especiales')

@section('content')
<section class="content-header">
    <div class="container-fluid">
        <h1>Permisos especiales de: {{ $usuario->name }}</h1>
    </div>
</section>

<section class="content">
    <div class="container-fluid">
        @include('mensajes')

        <div class="card">
            <div class="card-body">
                <form action="{{ route('usuarios.permisos.update', $usuario->id) }}" method="POST">
                    @csrf

                    <p class="text-muted">
                        Marca permisos directos para este usuario. Los permisos de rol siguen funcionando aparte.
                    </p>

                    <div class="row">
                        @foreach($permisos as $permiso)
                            @php
                                $isDirect = in_array($permiso->name, $directPermissions, true);
                                $isViaRole = in_array($permiso->name, $rolePermissions, true);
                            @endphp

                            <div class="col-md-4 mb-2">
                                <div class="form-check">
                                    <input class="form-check-input"
                                           type="checkbox"
                                           name="permisos[]"
                                           id="permiso-{{ $permiso->id }}"
                                           value="{{ $permiso->name }}"
                                           {{ $isDirect ? 'checked' : '' }}>

                                    <label class="form-check-label" for="permiso-{{ $permiso->id }}">
                                        {{ $permiso->name }}
                                    </label>

                                    @if($isViaRole)
                                        <span class="badge badge-secondary">vía rol</span>
                                    @endif
                                    @if($isDirect)
                                        <span class="badge badge-info">directo</span>
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
</section>
@endsection
