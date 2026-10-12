@extends('layouts.app')

@section('titulo', 'Consultar solicitud')

@section('contenido')
<h1>Consultar solicitud</h1>

<form method="GET" action="{{ route('solicitudes.consultar') }}" class="tarjeta" novalidate>
    <div class="campo">
        <label for="codigo">Código de la solicitud *</label>
        <input id="codigo" name="codigo" type="text" value="{{ old('codigo') }}" required
               placeholder="SOL-20261011-AB3K7Q" autocomplete="off" class="codigo"
               aria-describedby="ayuda-codigo @error('codigo') error-codigo @enderror"
               @error('codigo') aria-invalid="true" @enderror>
        <p class="ayuda" id="ayuda-codigo">Es el código que recibió al registrar la solicitud.</p>
        @error('codigo')<p class="error-campo" id="error-codigo">{{ $message }}</p>@enderror
    </div>
    <button type="submit">Consultar</button>
</form>
@endsection
