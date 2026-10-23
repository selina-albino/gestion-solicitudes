<?php

namespace Tests\Feature;

use App\Solicitudes\ServicioSolicitudes;
use Tests\TestCase;

/** RF9, RF10 · Vencimiento visto por HTTP y por el comando programado */
class VencimientoTest extends TestCase
{
    private function registrarCritica(): string
    {
        return app(ServicioSolicitudes::class)->registrar([
            'solicitante' => 'Laboratorio 2',
            'categoria' => 'redes',
            'descripcion' => 'Sin internet en todo el laboratorio de cómputo.',
            'prioridad' => 'critica',
        ])->codigo;
    }

    /** PI-04 · RF9 · La página de la solicitud muestra el plazo y luego la marca de vencida */
    public function test_pi04_pagina_muestra_plazo_y_marca_vencida(): void
    {
        $this->travelTo(now()->setDate(2026, 10, 22)->setTime(8, 0));
        $codigo = $this->registrarCritica();

        $this->get(route('solicitudes.show', $codigo))
            ->assertOk()
            ->assertSee('Plazo de atención')
            ->assertSee('22/10/2026 12:00')
            ->assertDontSee('VENCIDA');

        $this->travel(4)->hours();
        $this->travel(1)->minutes();

        $this->get(route('solicitudes.show', $codigo))
            ->assertOk()
            ->assertSee('VENCIDA:')
            ->assertSee('no fue atendida dentro de 4 horas')
            ->assertSee('Debe atenderse de inmediato.');

        $guardada = $this->solicitudesGuardadas()[$codigo];
        $this->assertNotNull($guardada['vencida_en']);
        $this->assertSame('Vencimiento', end($guardada['historial'])['accion']);
    }

    /** PI-05 · RF10 · El comando marca solo las críticas vencidas e informa cuáles */
    public function test_pi05_comando_marca_vencidas(): void
    {
        $critica = $this->registrarCritica();
        $alta = app(ServicioSolicitudes::class)->registrar([
            'solicitante' => 'Secretaría', 'categoria' => 'software',
            'descripcion' => 'El sistema de notas no exporta a PDF.', 'prioridad' => 'alta',
        ])->codigo;

        $this->artisan('solicitudes:marcar-vencidas')
            ->expectsOutput('No hay solicitudes nuevas para marcar como vencidas.')
            ->assertExitCode(0);

        $this->travel(5)->hours();

        $this->artisan('solicitudes:marcar-vencidas')
            ->expectsOutput('1 solicitud marcada como vencida:')
            ->assertExitCode(0);

        $guardadas = $this->solicitudesGuardadas();
        $this->assertNotNull($guardadas[$critica]['vencida_en']);
        $this->assertNull($guardadas[$alta]['vencida_en']);
    }

    /** PI-06 · RF10 · El comando queda programado para correr cada minuto */
    public function test_pi06_comando_esta_programado_cada_minuto(): void
    {
        $this->artisan('schedule:list')
            ->expectsOutputToContain('solicitudes:marcar-vencidas')
            ->assertExitCode(0);

        $evento = collect(app(\Illuminate\Console\Scheduling\Schedule::class)->events())
            ->first(fn ($e) => str_contains($e->command, 'solicitudes:marcar-vencidas'));
        $this->assertSame('* * * * *', $evento->expression);
    }

    /** PD-01 · D-01 · Fechas en hora de Bolivia (antes se guardaban y mostraban en UTC) */
    public function test_pd01_fechas_en_hora_de_bolivia(): void
    {
        $this->assertSame('America/La_Paz', config('app.timezone'));

        // 14:00 en UTC son las 10:00 en Cochabamba.
        $this->travelTo(\Illuminate\Support\Carbon::parse('2026-10-22 14:00:00', 'UTC'));
        $codigo = $this->registrarCritica();

        $this->assertStringEndsWith('-04:00', $this->solicitudesGuardadas()[$codigo]['creada_en']);
        $this->get(route('solicitudes.show', $codigo))
            ->assertSee('22/10/2026 10:00')
            ->assertSee('22/10/2026 14:00'); // plazo: 10:00 + 4 h
    }
}
