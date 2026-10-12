<?php

namespace Tests\Feature;

use App\Solicitudes\Mensajes;
use Tests\TestCase;

/** PE-02 · Caso de consulta con código inexistente (RF3, RF8) */
class CodigoInexistenteTest extends TestCase
{
    public function test_pe02_consultar_codigo_inexistente_informa_que_no_existe(): void
    {
        $this->get(route('solicitudes.consultar', ['codigo' => 'SOL-20260101-ZZZZZZ']))
            ->assertRedirect(route('solicitudes.buscar'))
            ->assertSessionHasErrors([
                'codigo' => 'No existe ninguna solicitud con el código SOL-20260101-ZZZZZZ. Revise que esté bien escrito.',
            ]);

        $this->followingRedirects()
            ->get(route('solicitudes.consultar', ['codigo' => 'sol-20260101-zzzzzz']))
            ->assertOk()
            ->assertSee('No existe ninguna solicitud con el código SOL-20260101-ZZZZZZ');
    }

    public function test_abrir_la_url_de_un_codigo_inexistente_tambien_informa(): void
    {
        $this->get(route('solicitudes.show', 'SOL-20260101-ZZZZZZ'))
            ->assertRedirect(route('solicitudes.buscar'))
            ->assertSessionHasErrors('codigo');
    }

    public function test_codigo_con_formato_incorrecto_muestra_ejemplo(): void
    {
        $this->get(route('solicitudes.consultar', ['codigo' => '12345']))
            ->assertRedirect(route('solicitudes.buscar'))
            ->assertSessionHasErrors(['codigo' => Mensajes::de('codigo', 'regex')]);
    }
}
