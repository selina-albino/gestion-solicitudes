<?php

namespace Tests\Feature;

use App\Solicitudes\Mensajes;
use Tests\TestCase;

/** PE-01 · Caso de error por datos incompletos (RF7, RF8) */
class DatosIncompletosTest extends TestCase
{
    public function test_pe01_formulario_vacio_muestra_un_mensaje_claro_por_campo_y_no_guarda(): void
    {
        $formulario = route('solicitudes.create');

        $this->from($formulario)
            ->post(route('solicitudes.store'), [
                'solicitante' => '',
                'categoria' => '',
                'descripcion' => '',
                'prioridad' => '',
            ])
            ->assertRedirect($formulario)
            ->assertSessionHasErrors([
                'solicitante' => Mensajes::de('solicitante', 'required'),
                'categoria' => Mensajes::de('categoria', 'required'),
                'descripcion' => Mensajes::de('descripcion', 'required'),
                'prioridad' => Mensajes::de('prioridad', 'required'),
            ]);

        $this->assertSame([], $this->solicitudesGuardadas());

        // El usuario ve los mensajes en español al volver al formulario.
        $this->followRedirects($this->from($formulario)->post(route('solicitudes.store'), [
            'solicitante' => 'Ana',
            'descripcion' => 'corta',
        ]))
            ->assertOk()
            ->assertSee('Revise los siguientes datos')
            ->assertSee(Mensajes::de('descripcion', 'min'))
            ->assertSee(Mensajes::de('categoria', 'required'));
    }

    public function test_valores_fuera_de_la_lista_son_rechazados(): void
    {
        $this->from(route('solicitudes.create'))
            ->post(route('solicitudes.store'), [
                'solicitante' => 'María López',
                'categoria' => 'cocina',
                'descripcion' => 'Descripción suficientemente larga.',
                'prioridad' => 'urgentisima',
            ])
            ->assertSessionHasErrors([
                'categoria' => Mensajes::de('categoria', 'in'),
                'prioridad' => Mensajes::de('prioridad', 'in'),
            ]);

        $this->assertSame([], $this->solicitudesGuardadas());
    }
}
