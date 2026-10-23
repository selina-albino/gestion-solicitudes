@extends('layouts.app')

@section('titulo', $solicitud->codigo)

@section('contenido')
<h1>Solicitud <span class="codigo">{{ $solicitud->codigo }}</span></h1>

@if ($solicitud->estaVencida())
    {{-- RF9: el texto "VENCIDA" acompaña al color, nunca solo el color. --}}
    <div class="aviso aviso-error" role="status">
        <strong>VENCIDA:</strong> esta solicitud {{ mb_strtolower($solicitud->prioridad->etiqueta()) }}
        no fue atendida dentro de {{ $horasLimite }} horas
        (plazo hasta {{ $limiteAtencion?->format('d/m/Y H:i') }}).
        @if ($solicitud->estado->esperaAtencion())
            Debe atenderse de inmediato.
        @endif
    </div>
@endif

<section class="tarjeta" aria-labelledby="titulo-datos">
    <h2 id="titulo-datos">Datos</h2>
    <dl class="datos">
        <dt>Estado</dt>        <dd><strong>{{ $solicitud->estado->etiqueta() }}</strong>@if ($solicitud->estaVencida()) · <strong class="etiqueta-vencida">Vencida</strong>@endif</dd>
        <dt>Solicitante</dt>   <dd>{{ $solicitud->solicitante }}</dd>
        <dt>Categoría</dt>     <dd>{{ $solicitud->categoria->etiqueta() }}</dd>
        <dt>Prioridad</dt>     <dd>{{ $solicitud->prioridad->etiqueta() }}</dd>
        @if ($limiteAtencion)
            <dt>Plazo de atención</dt>
            <dd>Hasta <time datetime="{{ $limiteAtencion->format(DATE_ATOM) }}">{{ $limiteAtencion->format('d/m/Y H:i') }}</time> ({{ $horasLimite }} horas desde el registro)</dd>
        @endif
        <dt>Técnico</dt>       <dd>{{ $solicitud->tecnico ?? 'Sin asignar' }}</dd>
        <dt>Registrada</dt>    <dd><time datetime="{{ $solicitud->creadaEn }}">{{ \Illuminate\Support\Carbon::parse($solicitud->creadaEn)->tz(config('app.timezone'))->format('d/m/Y H:i') }}</time></dd>
        <dt>Descripción</dt>   <dd>{{ $solicitud->descripcion }}</dd>
    </dl>
</section>

<div class="grilla-2">
    <section class="tarjeta" aria-labelledby="titulo-tecnico">
        <h2 id="titulo-tecnico">Asignar técnico</h2>
        @if ($solicitud->estado->aceptaTecnico())
            <form method="POST" action="{{ route('solicitudes.tecnico', $solicitud->codigo) }}" novalidate>
                @csrf
                @method('PATCH')
                <div class="campo">
                    <label for="tecnico">Técnico *</label>
                    <select id="tecnico" name="tecnico" required
                            @error('tecnico') aria-invalid="true" aria-describedby="error-tecnico" @enderror>
                        <option value="">— Seleccione —</option>
                        @foreach ($tecnicos as $tecnico)
                            <option value="{{ $tecnico }}" @selected($solicitud->tecnico === $tecnico)>{{ $tecnico }}</option>
                        @endforeach
                    </select>
                    @error('tecnico')<p class="error-campo" id="error-tecnico">{{ $message }}</p>@enderror
                </div>
                <button type="submit">{{ $solicitud->tecnico ? 'Reasignar' : 'Asignar' }}</button>
            </form>
        @else
            <p class="ayuda">No se puede asignar técnico en estado {{ $solicitud->estado->etiqueta() }}.</p>
        @endif
    </section>

    <section class="tarjeta" aria-labelledby="titulo-estado">
        <h2 id="titulo-estado">Actualizar estado</h2>
        @php($siguientes = $solicitud->estado->siguientesPermitidos())
        @if ($siguientes !== [])
            <form method="POST" action="{{ route('solicitudes.estado', $solicitud->codigo) }}" novalidate>
                @csrf
                @method('PATCH')
                <div class="campo">
                    <label for="estado">Nuevo estado *</label>
                    <select id="estado" name="estado" required
                            @error('estado') aria-invalid="true" aria-describedby="error-estado" @enderror>
                        <option value="">— Seleccione —</option>
                        @foreach ($siguientes as $estado)
                            <option value="{{ $estado->value }}" @selected(old('estado') === $estado->value)>{{ $estado->etiqueta() }}</option>
                        @endforeach
                    </select>
                    @error('estado')<p class="error-campo" id="error-estado">{{ $message }}</p>@enderror
                </div>
                <div class="campo">
                    <label for="comentario">Comentario (opcional)</label>
                    <input id="comentario" name="comentario" type="text" maxlength="255" value="{{ old('comentario') }}">
                </div>
                <button type="submit">Actualizar estado</button>
            </form>
            @if ($solicitud->estado === \App\Solicitudes\Estado::Registrada)
                <p class="ayuda">Para pasar a <em>Asignada</em>, asigne un técnico.</p>
            @endif
        @else
            <p class="ayuda">La solicitud está {{ $solicitud->estado->etiqueta() }} y ya no admite cambios.</p>
        @endif
    </section>
</div>

<section class="tarjeta" aria-labelledby="titulo-historial">
    <h2 id="titulo-historial">Historial de cambios</h2>
    <div class="tabla-scroll">
        <table>
            <thead>
                <tr><th scope="col">Fecha</th><th scope="col">Acción</th><th scope="col">Detalle</th></tr>
            </thead>
            <tbody>
                @foreach (array_reverse($solicitud->historial) as $cambio)
                    <tr>
                        <td><time datetime="{{ $cambio['fecha'] }}">{{ \Illuminate\Support\Carbon::parse($cambio['fecha'])->tz(config('app.timezone'))->format('d/m/Y H:i') }}</time></td>
                        <td>{{ $cambio['accion'] }}</td>
                        <td>{{ $cambio['detalle'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</section>
@endsection
