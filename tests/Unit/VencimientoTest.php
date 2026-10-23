<?php

namespace Tests\Unit;

use App\Solicitudes\Estado;
use App\Solicitudes\GeneradorCodigo;
use App\Solicitudes\ServicioSolicitudes;
use App\Solicitudes\Solicitud;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

/**
 * RF9 · Solicitudes críticas no atendidas en 4 horas se marcan como vencidas.
 * Se usa un reloj controlado para "adelantar" el tiempo sin esperar.
 */
class VencimientoTest extends TestCase
{
    private const REGISTRO = '2026-10-22 08:00:00';

    private RepositorioEnMemoria $repositorio;

    private ServicioSolicitudes $servicio;

    private DateTimeImmutable $ahora;

    protected function setUp(): void
    {
        $this->repositorio = new RepositorioEnMemoria;
        $this->ahora = new DateTimeImmutable(self::REGISTRO);
        $this->servicio = new ServicioSolicitudes(
            $this->repositorio,
            new GeneradorCodigo,
            ['Ana Quiroga', 'Bruno Mendoza'],
            fn () => $this->ahora,
        );
    }

    private function registrar(string $prioridad = 'critica'): string
    {
        return $this->servicio->registrar([
            'solicitante' => 'Laboratorio 2',
            'categoria' => 'redes',
            'descripcion' => 'Sin internet en todo el laboratorio de cómputo.',
            'prioridad' => $prioridad,
        ])->codigo;
    }

    private function pasar(string $tiempo): void
    {
        $this->ahora = (new DateTimeImmutable(self::REGISTRO))->modify($tiempo);
    }

    /** @return list<string> */
    private function acciones(Solicitud $solicitud): array
    {
        return array_column($solicitud->historial, 'accion');
    }

    /** PU-10 · RF9 · Crítica sin atender después de 4 h 1 min → vencida */
    public function test_pu10_critica_sin_atender_pasadas_4_horas_queda_vencida(): void
    {
        $codigo = $this->registrar();
        $this->pasar('+4 hours +1 minute');

        $solicitud = $this->servicio->consultar($codigo);

        $this->assertTrue($solicitud->estaVencida());
        $this->assertSame('2026-10-22T12:00:00', substr($solicitud->vencidaEn, 0, 19), 'Vence en el límite, no al detectarse');
        $this->assertSame(Estado::Registrada, $solicitud->estado, 'Vencer no cambia el estado');
        $this->assertSame(['Registro', 'Vencimiento'], $this->acciones($solicitud));
        $this->assertStringContainsString('no fue atendida dentro de 4 horas', end($solicitud->historial)['detalle']);
        $this->assertNotNull($this->repositorio->datos[$codigo]['vencida_en'], 'La marca queda guardada');
    }

    /** PU-11 · RF9 · Valores límite: 3 h 59 min y 4 h 00 min todavía están a tiempo */
    public function test_pu11_justo_en_el_limite_todavia_no_vence(): void
    {
        $codigo = $this->registrar();

        foreach (['+3 hours +59 minutes', '+4 hours'] as $tiempo) {
            $this->pasar($tiempo);
            $this->assertFalse($this->servicio->consultar($codigo)->estaVencida(), "No debía vencer en {$tiempo}");
        }

        $this->pasar('+4 hours +1 second');
        $this->assertTrue($this->servicio->consultar($codigo)->estaVencida());
    }

    /** PU-12 · RF9 · Crítica atendida (En proceso) antes de 4 h → nunca vence */
    public function test_pu12_critica_atendida_a_tiempo_no_vence(): void
    {
        $codigo = $this->registrar();
        $this->pasar('+1 hour');
        $this->servicio->asignarTecnico($codigo, 'Ana Quiroga');
        $this->pasar('+3 hours +30 minutes');
        $atendida = $this->servicio->cambiarEstado($codigo, Estado::EnProceso);

        $this->assertSame('2026-10-22T11:30:00', substr($atendida->atendidaEn, 0, 19));

        $this->pasar('+2 days');
        $this->assertFalse($this->servicio->consultar($codigo)->estaVencida());
        $this->assertSame([], $this->servicio->marcarVencidas());
    }

    /** PU-13 · RF9 · Prioridades distintas de Crítica no vencen */
    public function test_pu13_otras_prioridades_no_vencen(): void
    {
        $codigos = array_map(fn ($p) => $this->registrar($p), ['baja', 'media', 'alta']);
        $this->pasar('+3 days');

        foreach ($codigos as $codigo) {
            $solicitud = $this->servicio->consultar($codigo);
            $this->assertFalse($solicitud->estaVencida());
            $this->assertNull($this->servicio->limiteAtencion($solicitud));
        }
    }

    /** PU-14 · RF9 · Asignar técnico no es atender: si no pasa a En proceso, vence igual */
    public function test_pu14_asignar_tecnico_no_detiene_el_plazo(): void
    {
        $codigo = $this->registrar();
        $this->pasar('+30 minutes');
        $this->servicio->asignarTecnico($codigo, 'Bruno Mendoza');
        $this->pasar('+5 hours');

        $solicitud = $this->servicio->consultar($codigo);

        $this->assertTrue($solicitud->estaVencida());
        $this->assertSame(Estado::Asignada, $solicitud->estado);
    }

    /** PU-15 · RF10 · La revisión masiva marca solo las nuevas y no repite el historial */
    public function test_pu15_marcar_vencidas_no_duplica(): void
    {
        $critica1 = $this->registrar();
        $critica2 = $this->registrar();
        $this->registrar('alta');
        $this->pasar('+1 hour');
        $this->servicio->asignarTecnico($critica2, 'Ana Quiroga');
        $this->servicio->cambiarEstado($critica2, Estado::EnProceso);
        $this->pasar('+6 hours');

        $primera = $this->servicio->marcarVencidas();
        $segunda = $this->servicio->marcarVencidas();

        $this->assertSame([$critica1], array_map(fn (Solicitud $s) => $s->codigo, $primera));
        $this->assertSame([], $segunda);
        $this->assertSame(1, array_count_values($this->acciones($this->servicio->consultar($critica1)))['Vencimiento']);
    }

    /** PU-16 · RF9 · Cancelada antes del plazo → no vence */
    public function test_pu16_cancelada_antes_del_plazo_no_vence(): void
    {
        $codigo = $this->registrar();
        $this->pasar('+1 hour');
        $this->servicio->cambiarEstado($codigo, Estado::Cancelada, 'Duplicada');
        $this->pasar('+10 hours');

        $this->assertFalse($this->servicio->consultar($codigo)->estaVencida());
    }

    /** PU-17 · RF9 · Una vencida sigue su ciclo y conserva la marca como registro del incumplimiento */
    public function test_pu17_vencida_se_puede_atender_y_cerrar_sin_perder_la_marca(): void
    {
        $codigo = $this->registrar();
        $this->pasar('+5 hours');
        $this->servicio->asignarTecnico($codigo, 'Ana Quiroga');
        $this->servicio->cambiarEstado($codigo, Estado::EnProceso);
        $this->servicio->cambiarEstado($codigo, Estado::Resuelta);
        $cerrada = $this->servicio->cambiarEstado($codigo, Estado::Cerrada);

        $this->assertSame(Estado::Cerrada, $cerrada->estado);
        $this->assertTrue($cerrada->estaVencida());
        $this->assertSame(
            ['Registro', 'Vencimiento', 'Asignación de técnico', 'Cambio de estado', 'Cambio de estado', 'Cambio de estado'],
            $this->acciones($cerrada),
            'El vencimiento se registra antes de la acción que lo descubrió',
        );
    }

    /** PU-18 · Compatibilidad: solicitudes guardadas por la v1.0 (sin campos nuevos) se leen sin error */
    public function test_pu18_lee_solicitudes_guardadas_por_la_version_anterior(): void
    {
        $solicitud = Solicitud::fromArray([
            'codigo' => 'SOL-20261011-AB3K7Q', 'solicitante' => 'Juan Pérez', 'categoria' => 'redes',
            'descripcion' => 'Sin internet en la oficina 3.', 'prioridad' => 'critica', 'estado' => 'en_proceso',
            'tecnico' => 'Ana Quiroga', 'creada_en' => '2026-10-11T10:00:00+00:00', 'historial' => [],
        ]);

        $this->assertNull($solicitud->atendidaEn);
        $this->assertFalse($solicitud->estaVencida());
    }
}
