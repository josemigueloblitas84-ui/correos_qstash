@php
    $selectedSedeId = $selectedSedeId ?? old('sede_id');
@endphp

<div class="row">
    <div class="col-md-6">
        <div class="mb-3">
            <label for="sede_id" class="form-label fw-bold">Sede:</label>
            <select
                class="form-select @error('sede_id') is-invalid @enderror"
                id="sede_id"
                name="sede_id">
                <option value="">Seleccione una sede</option>
                @foreach ($sedes as $sede)
                    <option value="{{ $sede->id }}" {{ (string) $selectedSedeId === (string) $sede->id ? 'selected' : '' }}>
                        {{ $sede->nombre }}
                    </option>
                @endforeach
            </select>
            @error('sede_id')
                <div class="invalid-feedback d-block">
                    {{ $message }}
                </div>
            @enderror
        </div>
    </div>
</div>
