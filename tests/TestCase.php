<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    private string $archivoPrueba;

    protected function setUp(): void
    {
        parent::setUp();

        // Cada prueba usa su propio archivo JSON temporal: no toca los datos reales.
        $this->archivoPrueba = sys_get_temp_dir().'/solicitudes_test_'.uniqid().'.json';
        config(['solicitudes.archivo' => $this->archivoPrueba]);
    }

    protected function tearDown(): void
    {
        if (is_file($this->archivoPrueba)) {
            unlink($this->archivoPrueba);
        }

        parent::tearDown();
    }

    /** Lee el archivo JSON de la prueba para verificar lo que realmente se guardó. */
    protected function solicitudesGuardadas(): array
    {
        if (! is_file($this->archivoPrueba)) {
            return [];
        }

        return json_decode(file_get_contents($this->archivoPrueba), true) ?? [];
    }
}
