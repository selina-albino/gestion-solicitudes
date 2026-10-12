<?php

namespace Tests\Feature;

use Tests\TestCase;

/** PI-01 · Integración: formulario → validación → servicio → archivo JSON → vista */
class RegistrarSolicitudTest extends TestCase
{
    public function test_pi01_registra_por_http_guarda_en_json_y_muestra_el_codigo(): void
    {
        $respuesta = $this->from(route('solicitudes.create'))->post(route('solicitudes.store'), [
            'solicitante' => 'María López',
            'categoria' => 'software',
            'descripcion' => 'El sistema contable muestra error al exportar.',
            'prioridad' => 'media',
        ]);

        $guardadas = $this->solicitudesGuardadas();
        $this->assertCount(1, $guardadas);

        $codigo = array_key_first($guardadas);
        $respuesta->assertRedirect(route('solicitudes.show', $codigo))
            ->assertSessionHasNoErrors()
            ->assertSessionHas('exito');

        $this->assertSame('registrada', $guardadas[$codigo]['estado']);
        $this->assertSame('Registro', $guardadas[$codigo]['historial'][0]['accion']);

        $this->get(route('solicitudes.show', $codigo))
            ->assertOk()
            ->assertSee($codigo)
            ->assertSee('María López')
            ->assertSee('Sin asignar');
    }
}
