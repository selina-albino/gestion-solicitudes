<?php

namespace Tests\Unit;

use App\Solicitudes\GeneradorCodigo;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

/** PU-01 y PU-02 · RF2 Generar un código único */
class GeneradorCodigoTest extends TestCase
{
    public function test_pu01_genera_codigo_con_formato_y_fecha(): void
    {
        $codigo = (new GeneradorCodigo)->generar(new DateTimeImmutable('2026-10-11 09:30'));

        $this->assertStringStartsWith('SOL-20261011-', $codigo);
        $this->assertTrue(GeneradorCodigo::esValido($codigo), "Formato inválido: {$codigo}");
    }

    public function test_pu02_genera_codigos_distintos_en_muchas_llamadas(): void
    {
        $generador = new GeneradorCodigo;
        $fecha = new DateTimeImmutable('2026-10-11');
        $codigos = [];

        for ($i = 0; $i < 1000; $i++) {
            $codigos[] = $generador->generar($fecha);
        }

        $this->assertCount(1000, array_unique($codigos));
    }

    public function test_normaliza_codigo_escrito_en_minusculas_y_con_espacios(): void
    {
        $this->assertSame('SOL-20261011-AB3K7Q', GeneradorCodigo::normalizar('  sol-20261011-ab3k7q '));
    }
}
