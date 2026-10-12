<?php

namespace Tests\Unit;

use App\Solicitudes\Categoria;
use App\Solicitudes\Estado;
use App\Solicitudes\Prioridad;
use App\Solicitudes\RepositorioJson;
use App\Solicitudes\Solicitud;
use PHPUnit\Framework\TestCase;

/** PU-09 · RF6 Conservar historial (persistencia) */
class RepositorioJsonTest extends TestCase
{
    private string $ruta;

    protected function setUp(): void
    {
        $this->ruta = sys_get_temp_dir().'/repo_test_'.uniqid().'/solicitudes.json';
    }

    protected function tearDown(): void
    {
        if (is_file($this->ruta)) {
            unlink($this->ruta);
            rmdir(dirname($this->ruta));
        }
    }

    public function test_pu09_guarda_y_recupera_la_solicitud_con_su_historial(): void
    {
        $repositorio = new RepositorioJson($this->ruta);
        $solicitud = new Solicitud(
            'SOL-20261011-AB3K7Q', 'Juan Pérez', Categoria::Redes, 'Sin internet en la oficina 3.',
            Prioridad::Critica, Estado::Registrada, null, '2026-10-11T10:00:00-04:00',
        );
        $solicitud->registrarCambio('2026-10-11T10:00:00-04:00', 'Registro', 'Alta', null);

        $repositorio->guardar($solicitud);
        $recuperada = (new RepositorioJson($this->ruta))->buscar('SOL-20261011-AB3K7Q');

        $this->assertNotNull($recuperada);
        $this->assertSame($solicitud->toArray(), $recuperada->toArray());
        $this->assertTrue($repositorio->existe('SOL-20261011-AB3K7Q'));
        $this->assertNull($repositorio->buscar('SOL-20261011-NOEXST'));
    }
}
