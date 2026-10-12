<?php

namespace App\Solicitudes;

use RuntimeException;

/**
 * Guarda las solicitudes en un archivo JSON indexado por código.
 */
final class RepositorioJson implements RepositorioSolicitudes
{
    public function __construct(private readonly string $ruta) {}

    public function guardar(Solicitud $solicitud): void
    {
        $todas = $this->leerTodas();
        $todas[$solicitud->codigo] = $solicitud->toArray();
        $this->escribirTodas($todas);
    }

    public function buscar(string $codigo): ?Solicitud
    {
        $datos = $this->leerTodas()[$codigo] ?? null;

        return $datos === null ? null : Solicitud::fromArray($datos);
    }

    public function existe(string $codigo): bool
    {
        return array_key_exists($codigo, $this->leerTodas());
    }

    private function leerTodas(): array
    {
        if (! is_file($this->ruta)) {
            return [];
        }

        $contenido = file_get_contents($this->ruta);

        if ($contenido === false || trim($contenido) === '') {
            return [];
        }

        $datos = json_decode($contenido, true);

        if (! is_array($datos)) {
            throw new RuntimeException('El archivo de solicitudes está dañado: '.$this->ruta);
        }

        return $datos;
    }

    private function escribirTodas(array $todas): void
    {
        $directorio = dirname($this->ruta);

        if (! is_dir($directorio) && ! mkdir($directorio, 0775, true) && ! is_dir($directorio)) {
            throw new RuntimeException('No se pudo crear la carpeta de datos: '.$directorio);
        }

        $json = json_encode($todas, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);

        if (file_put_contents($this->ruta, $json, LOCK_EX) === false) {
            throw new RuntimeException('No se pudo guardar el archivo de solicitudes: '.$this->ruta);
        }
    }
}
