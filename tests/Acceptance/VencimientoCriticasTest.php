<?php

namespace Tests\Acceptance;

use Tests\TestCase;

/**
 * PA-02 · Prueba de aceptación del nuevo requisito (RF9, RF10).
 *
 * Historia: "Como coordinador de la mesa de servicios quiero que las solicitudes
 * críticas no atendidas en cuatro horas queden marcadas como vencidas, para
 * atenderlas de inmediato y medir el cumplimiento."
 */
class VencimientoCriticasTest extends TestCase
{
    private function registrar(string $solicitante): string
    {
        $this->post(route('solicitudes.store'), [
            'solicitante' => $solicitante,
            'categoria' => 'redes',
            'descripcion' => 'Caída de la red en el edificio de aulas.',
            'prioridad' => 'critica',
        ]);

        $guardadas = $this->solicitudesGuardadas();
        foreach ($guardadas as $codigo => $datos) {
            if ($datos['solicitante'] === $solicitante) {
                return $codigo;
            }
        }
        $this->fail("No se registró la solicitud de {$solicitante}");
    }

    public function test_pa02_critica_a_tiempo_y_critica_vencida(): void
    {
        $this->travelTo(now()->setDate(2026, 10, 22)->setTime(8, 0));

        // 1. Llegan dos solicitudes críticas a las 08:00.
        $aTiempo = $this->registrar('Aula Magna');
        $tarde = $this->registrar('Biblioteca');

        // 2. A las 09:00 se asigna técnico a ambas, pero solo una pasa a En proceso.
        $this->travel(1)->hours();
        foreach ([$aTiempo, $tarde] as $codigo) {
            $this->patch(route('solicitudes.tecnico', $codigo), ['tecnico' => 'Carla Rojas'])->assertSessionHasNoErrors();
        }
        $this->patch(route('solicitudes.estado', $aTiempo), ['estado' => 'en_proceso'])->assertSessionHasNoErrors();

        // 3. A las 12:30 corre la tarea programada.
        $this->travel(210)->minutes();
        $this->artisan('solicitudes:marcar-vencidas')->expectsOutput('1 solicitud marcada como vencida:');

        // 4. El coordinador ve la diferencia en cada solicitud.
        $this->get(route('solicitudes.show', $aTiempo))->assertOk()->assertDontSee('VENCIDA');
        $this->get(route('solicitudes.show', $tarde))->assertOk()
            ->assertSee('VENCIDA:')
            ->assertSee('plazo hasta 22/10/2026 12:00');

        // 5. La vencida igual se atiende y se cierra; la marca queda como registro.
        $pagina = route('solicitudes.show', $tarde);
        foreach (['en_proceso', 'resuelta', 'cerrada'] as $estado) {
            $this->from($pagina)->patch(route('solicitudes.estado', $tarde), ['estado' => $estado])->assertSessionHasNoErrors();
        }

        $this->get($pagina)->assertOk()
            ->assertSee('Cerrada')
            ->assertSee('VENCIDA:')
            ->assertDontSee('Debe atenderse de inmediato.')
            ->assertSeeInOrder(['Estado cambiado de Resuelta a Cerrada.', 'Vencimiento', 'Técnico asignado: Carla Rojas.']);
    }
}
