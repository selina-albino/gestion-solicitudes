@extends('layouts.app')

@section('titulo', 'Nueva solicitud')

@section('contenido')
<h1>Registrar solicitud</h1>

{{-- novalidate: la validación la hace el servidor para mostrar siempre los mismos mensajes. --}}
<form method="POST" action="{{ route('solicitudes.store') }}" class="tarjeta" novalidate>
    @csrf

    <div class="campo">
        <label for="solicitante">Solicitante *</label>
        <input id="solicitante" name="solicitante" type="text" value="{{ old('solicitante') }}"
               maxlength="100" required autocomplete="name"
               @error('solicitante') aria-invalid="true" aria-describedby="error-solicitante" @enderror>
        @error('solicitante')<p class="error-campo" id="error-solicitante">{{ $message }}</p>@enderror
    </div>

    <div class="grilla-2">
        <div class="campo">
            <label for="categoria">Categoría *</label>
            <select id="categoria" name="categoria" required
                    @error('categoria') aria-invalid="true" aria-describedby="error-categoria" @enderror>
                <option value="">— Seleccione —</option>
                @foreach ($categorias as $categoria)
                    <option value="{{ $categoria->value }}" @selected(old('categoria') === $categoria->value)>{{ $categoria->etiqueta() }}</option>
                @endforeach
            </select>
            @error('categoria')<p class="error-campo" id="error-categoria">{{ $message }}</p>@enderror
        </div>

        <div class="campo">
            <label for="prioridad">Prioridad *</label>
            <select id="prioridad" name="prioridad" required
                    @error('prioridad') aria-invalid="true" aria-describedby="error-prioridad" @enderror>
                <option value="">— Seleccione —</option>
                @foreach ($prioridades as $prioridad)
                    <option value="{{ $prioridad->value }}" @selected(old('prioridad') === $prioridad->value)>{{ $prioridad->etiqueta() }}</option>
                @endforeach
            </select>
            @error('prioridad')<p class="error-campo" id="error-prioridad">{{ $message }}</p>@enderror
        </div>
    </div>

    <div class="campo">
        <label for="descripcion">Descripción *</label>
        <textarea id="descripcion" name="descripcion" maxlength="1000" required
                  aria-describedby="ayuda-descripcion @error('descripcion') error-descripcion @enderror"
                  @error('descripcion') aria-invalid="true" @enderror>{{ old('descripcion') }}</textarea>
        <p class="ayuda" id="ayuda-descripcion">Entre 10 y 1000 caracteres. Indique qué pasa y desde cuándo.</p>
        @error('descripcion')<p class="error-campo" id="error-descripcion">{{ $message }}</p>@enderror
    </div>

    <p class="ayuda">Los campos con * son obligatorios.</p>
    <button type="submit">Registrar solicitud</button>
</form>
@endsection
