<?php

use App\Solicitudes\Indicadores;
use App\Solicitudes\Prioridad;
use App\Solicitudes\RepositorioSolicitudes;
use App\Solicitudes\ServicioSolicitudes;
use App\Solicitudes\Solicitud;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

/*
| RF9 · Marca como vencidas las solicitudes críticas que pasaron el plazo de atención.
| Se puede ejecutar a mano:  php artisan solicitudes:marcar-vencidas
| En el servidor corre solo cada minuto con:  php artisan schedule:work
*/
Artisan::command('solicitudes:marcar-vencidas', function (ServicioSolicitudes $servicio) {
    $vencidas = $servicio->marcarVencidas();

    if ($vencidas === []) {
        $this->info('No hay solicitudes nuevas para marcar como vencidas.');

        return;
    }

    $this->warn(count($vencidas) === 1
        ? '1 solicitud marcada como vencida:'
        : count($vencidas).' solicitudes marcadas como vencidas:');

    $this->table(
        ['Código', 'Estado', 'Técnico', 'Plazo vencido'],
        array_map(fn (Solicitud $s) => [
            $s->codigo,
            $s->estado->etiqueta(),
            $s->tecnico ?? 'Sin asignar',
            $servicio->limiteAtencion($s)?->format('d/m/Y H:i'),
        ], $vencidas),
    );
})->purpose('Marca como vencidas las solicitudes críticas no atendidas a tiempo');

Schedule::command('solicitudes:marcar-vencidas')->everyMinute()->withoutOverlapping();

/*
| Indicadores de mantenimiento: php artisan solicitudes:indicadores
*/
Artisan::command('solicitudes:indicadores', function (RepositorioSolicitudes $repositorio) {
    $indicadores = new Indicadores($repositorio->todas());
    $horas = fn (?float $minutos) => $minutos === null ? 'sin datos' : number_format($minutos / 60, 2, ',', '.').' h';

    $this->line('<options=bold>Solicitudes abiertas por estado</>');
    $abiertas = $indicadores->abiertasPorEstado();
    $this->table(['Estado', 'Cantidad'], array_map(null, array_keys($abiertas), array_values($abiertas)));

    $criticas = $indicadores->cumplimientoCriticas();
    $this->table(['Indicador', 'Valor'], [
        ['Tiempo promedio de respuesta (registro → En proceso)', $horas($indicadores->minutosPromedioRespuesta())],
        ['Tiempo promedio de respuesta, solo críticas', $horas($indicadores->minutosPromedioRespuesta(Prioridad::Critica))],
        ['Solicitudes reabiertas (Resuelta → En proceso)', $indicadores->reabiertas()],
        ['Críticas registradas / vencidas', "{$criticas['criticas']} / {$criticas['vencidas']}"],
        ['Cumplimiento del plazo en críticas', $criticas['cumplimiento'] === null ? 'sin datos' : number_format($criticas['cumplimiento'], 1, ',', '.').' %'],
    ]);
})->purpose('Muestra los indicadores de seguimiento de la mesa de servicios');
