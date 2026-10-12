<?php

namespace Tests\Unit;

use App\Solicitudes\Estado;
use App\Solicitudes\Excepciones\DatosInvalidos;
use App\Solicitudes\Excepciones\OperacionNoPermitida;
use App\Solicitudes\Excepciones\SolicitudNoEncontrada;
use App\Solicitudes\GeneradorCodigo;
use App\Solicitudes\Mensajes;
use App\Solicitudes\ServicioSolicitudes;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

class ServicioSolicitudesTest extends TestCase
{
    private RepositorioEnMemoria $repositorio;

    private ServicioSolicitudes $servicio;

    protected function setUp(): void
    {
        $this->repositorio = new RepositorioEnMemoria;
        $this->servicio = new ServicioSolicitudes(
            $this->repositorio,
            new GeneradorCodigo,
            ['Ana Quiroga', 'Bruno Mendoza'],
            fn () => new DateTimeImmutable('2026-10-11 10:00:00'),
        );
    }

    private function datosValidos(array $cambios = []): array
    {
        return array_merge([
            'solicitante' => 'María López',
            'categoria' => 'hardware',
            'descripcion' => 'La impresora del segundo piso no enciende.',
            'prioridad' => 'alta',
        ], $cambios);
    }

    /** PU-03 · RF1, RF2, RF6 */
    public function test_pu03_registra_solicitud_en_estado_registrada_con_historial(): void
    {
        $solicitud = $this->servicio->registrar($this->datosValidos());

        $this->assertTrue(GeneradorCodigo::esValido($solicitud->codigo));
        $this->assertSame(Estado::Registrada, $solicitud->estado);
        $this->assertNull($solicitud->tecnico);
        $this->assertCount(1, $solicitud->historial);
        $this->assertSame('Registro', $solicitud->historial[0]['accion']);
        $this->assertArrayHasKey($solicitud->codigo, $this->repositorio->datos);
    }

    /** PE-01 (unitaria) · RF7, RF8 */
    public function test_pe01_rechaza_registro_con_datos_incompletos_y_explica_cada_campo(): void
    {
        try {
            $this->servicio->registrar(['solicitante' => '  ', 'descripcion' => 'corta']);
            $this->fail('Se esperaba DatosInvalidos');
        } catch (DatosInvalidos $e) {
            $this->assertSame([
                'solicitante' => Mensajes::de('solicitante', 'required'),
                'categoria' => Mensajes::de('categoria', 'required'),
                'descripcion' => Mensajes::de('descripcion', 'min'),
                'prioridad' => Mensajes::de('prioridad', 'required'),
            ], $e->errores);
        }

        $this->assertSame([], $this->repositorio->datos, 'No debe guardar nada si los datos son inválidos');
    }

    /** PU-04 · RF4, RF5, RF6 */
    public function test_pu04_asignar_tecnico_pasa_a_asignada_y_deja_historial(): void
    {
        $codigo = $this->servicio->registrar($this->datosValidos())->codigo;

        $solicitud = $this->servicio->asignarTecnico($codigo, 'Ana Quiroga');

        $this->assertSame('Ana Quiroga', $solicitud->tecnico);
        $this->assertSame(Estado::Asignada, $solicitud->estado);
        $this->assertCount(2, $solicitud->historial);
        $this->assertSame('registrada', $solicitud->historial[1]['estado_anterior']);
        $this->assertSame('asignada', $solicitud->historial[1]['estado_nuevo']);
    }

    /** PU-05 · RF5, RF8 */
    public function test_pu05_rechaza_transicion_de_estado_no_permitida_con_mensaje_claro(): void
    {
        $codigo = $this->servicio->registrar($this->datosValidos())->codigo;

        $this->expectException(OperacionNoPermitida::class);
        $this->expectExceptionMessage('No se puede pasar de Registrada a Resuelta');

        $this->servicio->cambiarEstado($codigo, Estado::Resuelta);
    }

    /** PU-06 · RF5, RF6 */
    public function test_pu06_cambio_de_estado_valido_guarda_comentario_en_historial(): void
    {
        $codigo = $this->servicio->registrar($this->datosValidos())->codigo;
        $this->servicio->asignarTecnico($codigo, 'Bruno Mendoza');

        $solicitud = $this->servicio->cambiarEstado($codigo, 'en_proceso', 'Revisando cableado');

        $this->assertSame(Estado::EnProceso, $solicitud->estado);
        $this->assertStringContainsString('Comentario: Revisando cableado', end($solicitud->historial)['detalle']);
    }

    /** PE-02 (unitaria) · RF3, RF8 */
    public function test_pe02_consultar_codigo_inexistente_lanza_excepcion_entendible(): void
    {
        $this->expectException(SolicitudNoEncontrada::class);
        $this->expectExceptionMessage('No existe ninguna solicitud con el código SOL-20260101-ZZZZZZ');

        $this->servicio->consultar('sol-20260101-zzzzzz');
    }

    /** PU-07 · RF2 */
    public function test_pu07_reintenta_si_el_codigo_generado_ya_existe(): void
    {
        $this->repositorio->colisionesSimuladas = 2;

        $solicitud = $this->servicio->registrar($this->datosValidos());

        $this->assertSame(0, $this->repositorio->colisionesSimuladas);
        $this->assertArrayHasKey($solicitud->codigo, $this->repositorio->datos);
    }

    /** PU-08 · RF4, RF8 */
    public function test_pu08_rechaza_tecnico_que_no_esta_en_la_lista(): void
    {
        $codigo = $this->servicio->registrar($this->datosValidos())->codigo;

        $this->expectException(DatosInvalidos::class);
        $this->expectExceptionMessage(Mensajes::de('tecnico', 'in'));

        $this->servicio->asignarTecnico($codigo, 'Persona Desconocida');
    }
}
