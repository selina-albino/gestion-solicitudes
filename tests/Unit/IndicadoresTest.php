<?php

namespace Tests\Unit;

use App\Solicitudes\Estado;
use App\Solicitudes\GeneradorCodigo;
use App\Solicitudes\Indicadores;
use App\Solicitudes\Prioridad;
use App\Solicitudes\ServicioSolicitudes;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

/** PU-19 · RF11 · Indicadores de seguimiento calculados desde el historial */
class IndicadoresTest extends TestCase
{
    public function test_pu19_calcula_abiertas_respuesta_reabiertas_y_cumplimiento(): void
    {
        $repositorio = new RepositorioEnMemoria;
        $ahora = new DateTimeImmutable('2026-10-22 08:00');
        $servicio = new ServicioSolicitudes($repositorio, new GeneradorCodigo, ['Ana Quiroga'], function () use (&$ahora) {
            return $ahora;
        });
        $nueva = fn (string $prioridad) => $servicio->registrar([
            'solicitante' => 'Docente', 'categoria' => 'software',
            'descripcion' => 'No abre el aula virtual desde el laboratorio.', 'prioridad' => $prioridad,
        ])->codigo;

        $a = $nueva('critica');   // atendida a la 1 h, resuelta y reabierta
        $b = $nueva('critica');   // nunca atendida → vence
        $c = $nueva('media');     // atendida a las 3 h
        $nueva('baja');           // queda registrada

        $ahora = $ahora->modify('+1 hour');
        $servicio->asignarTecnico($a, 'Ana Quiroga');
        $servicio->cambiarEstado($a, Estado::EnProceso);
        $servicio->cambiarEstado($a, Estado::Resuelta);
        $servicio->cambiarEstado($a, Estado::EnProceso, 'El problema volvió');

        $ahora = $ahora->modify('+2 hours');
        $servicio->asignarTecnico($c, 'Ana Quiroga');
        $servicio->cambiarEstado($c, Estado::EnProceso);

        $ahora = $ahora->modify('+2 hours');
        $servicio->marcarVencidas();

        $indicadores = new Indicadores($repositorio->todas());

        $this->assertSame(
            ['Registrada' => 2, 'Asignada' => 0, 'En proceso' => 2, 'Resuelta' => 0],
            $indicadores->abiertasPorEstado(),
        );
        $this->assertEqualsWithDelta(120.0, $indicadores->minutosPromedioRespuesta(), 0.01); // (60 + 180) / 2
        $this->assertEqualsWithDelta(60.0, $indicadores->minutosPromedioRespuesta(Prioridad::Critica), 0.01);
        $this->assertSame(1, $indicadores->reabiertas());
        $this->assertSame(['criticas' => 2, 'vencidas' => 1, 'cumplimiento' => 50.0], $indicadores->cumplimientoCriticas());
        $this->assertTrue($repositorio->buscar($b)->estaVencida());
    }
}
