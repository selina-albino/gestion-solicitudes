<?php

namespace Tests\Unit;

use App\Solicitudes\Categoria;
use App\Solicitudes\Estado;
use App\Solicitudes\Excepciones\DatosInvalidos;
use App\Solicitudes\GeneradorCodigo;
use App\Solicitudes\Mensajes;
use App\Solicitudes\Prioridad;
use App\Solicitudes\RepositorioJson;
use App\Solicitudes\ServicioSolicitudes;
use App\Solicitudes\Solicitud;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;
use PHPUnit\Framework\TestCase;

/**
 * Pruebas de regresión de los defectos encontrados al evaluar la versión 1.0.
 * Contra la v1.0 fallan; contra la v1.1 pasan.
 */
class DefectosCorregidosTest extends TestCase
{
    /** PD-02 · D-02 · Guardados simultáneos no pierden solicitudes */
    #[RequiresPhpExtension('pcntl')]
    public function test_pd02_guardados_simultaneos_no_pierden_datos(): void
    {
        $ruta = sys_get_temp_dir().'/concurrencia_'.uniqid().'.json';
        $procesos = 6;
        $porProceso = 20;
        $hijos = [];

        for ($p = 0; $p < $procesos; $p++) {
            $pid = pcntl_fork();
            if ($pid === 0) {
                $repositorio = new RepositorioJson($ruta);
                for ($i = 0; $i < $porProceso; $i++) {
                    $codigo = sprintf('SOL-20261022-%s%02d', ['A', 'B', 'C', 'D', 'E', 'F'][$p].'ZZZ', $i);
                    $repositorio->guardar(new Solicitud(
                        $codigo, 'Carga', Categoria::Otro, 'Guardado simultáneo', Prioridad::Media,
                        Estado::Registrada, null, '2026-10-22T08:00:00-04:00',
                    ));
                }
                exit(0);
            }
            $hijos[] = $pid;
        }

        foreach ($hijos as $pid) {
            pcntl_waitpid($pid, $estado);
        }

        $guardadas = (new RepositorioJson($ruta))->todas();
        @unlink($ruta);

        $this->assertCount($procesos * $porProceso, $guardadas);
    }

    /** PD-03 · D-03 · El servicio rechaza comentarios de más de 255 caracteres (igual que el formulario) */
    public function test_pd03_servicio_rechaza_comentario_demasiado_largo(): void
    {
        $repositorio = new RepositorioEnMemoria;
        $servicio = new ServicioSolicitudes(
            $repositorio, new GeneradorCodigo, ['Ana Quiroga'], fn () => new DateTimeImmutable('2026-10-22 08:00'),
        );
        $codigo = $servicio->registrar([
            'solicitante' => 'María López', 'categoria' => 'hardware',
            'descripcion' => 'La impresora del segundo piso no enciende.', 'prioridad' => 'media',
        ])->codigo;
        $servicio->asignarTecnico($codigo, 'Ana Quiroga');

        try {
            $servicio->cambiarEstado($codigo, Estado::EnProceso, str_repeat('a', 256));
            $this->fail('Se esperaba DatosInvalidos');
        } catch (DatosInvalidos $e) {
            $this->assertSame(['comentario' => Mensajes::de('comentario', 'max')], $e->errores);
        }

        $this->assertSame('asignada', $repositorio->datos[$codigo]['estado'], 'No debe cambiar nada');
        $this->assertSame(Estado::EnProceso, $servicio->cambiarEstado($codigo, Estado::EnProceso, str_repeat('a', 255))->estado);
    }

    /** PD-05 · D-05 · Pedir un mensaje que no existe no rompe la aplicación */
    public function test_pd05_mensaje_inexistente_devuelve_texto_generico(): void
    {
        $this->assertSame('El dato "comentario" no es válido.', Mensajes::de('comentario', 'required'));
    }
}
