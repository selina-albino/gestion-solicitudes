<?php

namespace Tests\Acceptance;

use Tests\TestCase;

/**
 * PA-01 · Prueba de aceptación del flujo completo (RF1–RF8).
 *
 * Historia: "Como usuario quiero registrar una solicitud, recibir un código,
 * consultarla con ese código y ver cómo avanza hasta cerrarse."
 */
class FlujoCompletoTest extends TestCase
{
    public function test_pa01_registrar_consultar_asignar_resolver_y_cerrar(): void
    {
        // 1. El usuario abre el formulario.
        $this->get('/')->assertRedirect('/solicitudes/nueva');
        $this->get(route('solicitudes.create'))->assertOk()->assertSee('Registrar solicitud');

        // 2. Registra la solicitud y recibe un código.
        $this->followingRedirects()
            ->post(route('solicitudes.store'), [
                'solicitante' => 'Lucía Fernández',
                'categoria' => 'accesos',
                'descripcion' => 'No puedo ingresar a mi correo institucional desde ayer.',
                'prioridad' => 'alta',
            ])
            ->assertOk()
            ->assertSee('Solicitud registrada. Guarde este código');

        $codigo = array_key_first($this->solicitudesGuardadas());
        $this->assertMatchesRegularExpression('/^SOL-\d{8}-[A-Z0-9]{6}$/', $codigo);

        // 3. Lo consulta con el código (aunque lo escriba en minúsculas).
        $this->followingRedirects()
            ->get(route('solicitudes.consultar', ['codigo' => strtolower($codigo)]))
            ->assertOk()
            ->assertSee($codigo)
            ->assertSee('Lucía Fernández')
            ->assertSee('Registrada');

        // 4. Soporte asigna un técnico.
        $pagina = route('solicitudes.show', $codigo);
        $this->followingRedirects()->from($pagina)
            ->patch(route('solicitudes.tecnico', $codigo), ['tecnico' => 'Diego Vargas'])
            ->assertSee('Técnico asignado: Diego Vargas');

        // 5. El estado avanza hasta cerrarse.
        foreach (['en_proceso' => 'En proceso', 'resuelta' => 'Resuelta', 'cerrada' => 'Cerrada'] as $valor => $etiqueta) {
            $this->followingRedirects()->from($pagina)
                ->patch(route('solicitudes.estado', $codigo), ['estado' => $valor])
                ->assertSee("Estado actualizado a {$etiqueta}.");
        }

        // 6. El historial muestra todo el recorrido y la solicitud ya no admite cambios.
        $this->get($pagina)
            ->assertOk()
            ->assertSee('Historial de cambios')
            ->assertSeeInOrder([
                'Estado cambiado de Resuelta a Cerrada.',
                'Estado cambiado de En proceso a Resuelta.',
                'Estado cambiado de Asignada a En proceso.',
                'Técnico asignado: Diego Vargas.',
                'Solicitud registrada por Lucía Fernández',
            ])
            ->assertSee('ya no admite cambios');

        $this->assertCount(5, $this->solicitudesGuardadas()[$codigo]['historial']);
    }
}
