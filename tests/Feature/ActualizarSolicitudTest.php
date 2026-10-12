<?php

namespace Tests\Feature;

use App\Solicitudes\ServicioSolicitudes;
use Tests\TestCase;

/** PI-02 · Integración: asignar técnico y cambiar estado por HTTP, con persistencia del historial */
class ActualizarSolicitudTest extends TestCase
{
    private string $codigo;

    protected function setUp(): void
    {
        parent::setUp();

        $this->codigo = app(ServicioSolicitudes::class)->registrar([
            'solicitante' => 'Juan Pérez',
            'categoria' => 'redes',
            'descripcion' => 'No hay conexión a internet en la oficina 3.',
            'prioridad' => 'critica',
        ])->codigo;
    }

    public function test_pi02_asigna_tecnico_cambia_estado_y_conserva_historial(): void
    {
        $pagina = route('solicitudes.show', $this->codigo);

        $this->from($pagina)
            ->patch(route('solicitudes.tecnico', $this->codigo), ['tecnico' => 'Carla Rojas'])
            ->assertRedirect($pagina)
            ->assertSessionHasNoErrors();

        $this->from($pagina)
            ->patch(route('solicitudes.estado', $this->codigo), ['estado' => 'en_proceso', 'comentario' => 'Revisando el router'])
            ->assertRedirect($pagina)
            ->assertSessionHasNoErrors();

        $guardada = $this->solicitudesGuardadas()[$this->codigo];
        $this->assertSame('Carla Rojas', $guardada['tecnico']);
        $this->assertSame('en_proceso', $guardada['estado']);
        $this->assertSame(
            ['Registro', 'Asignación de técnico', 'Cambio de estado'],
            array_column($guardada['historial'], 'accion'),
        );

        $this->get($pagina)
            ->assertOk()
            ->assertSee('En proceso')
            ->assertSee('Revisando el router');
    }

    public function test_cambio_de_estado_no_permitido_muestra_mensaje_y_no_modifica(): void
    {
        $pagina = route('solicitudes.show', $this->codigo);

        $this->from($pagina)
            ->patch(route('solicitudes.estado', $this->codigo), ['estado' => 'cerrada'])
            ->assertRedirect($pagina)
            ->assertSessionHasErrors(['estado' => 'No se puede pasar de Registrada a Cerrada. Desde Registrada solo se puede pasar a: Cancelada.']);

        $this->assertSame('registrada', $this->solicitudesGuardadas()[$this->codigo]['estado']);
    }
}
