<?php

namespace App\Solicitudes;

use RuntimeException;

/**
 * Guarda las solicitudes en un archivo JSON indexado por código.
 *
 * v1.1 (D-02): leer, modificar y escribir se hace con el archivo bloqueado de
 * principio a fin. Antes solo se bloqueaba la escritura, y dos guardados al mismo
 * tiempo (por ejemplo, un usuario y la tarea de vencimiento) podían perder datos.
 */
final class RepositorioJson implements RepositorioSolicitudes
{
    public function __construct(private readonly string $ruta) {}

    public function guardar(Solicitud $solicitud): void
    {
        $this->crearCarpeta();

        $archivo = fopen($this->ruta, 'c+');
        if ($archivo === false) {
            throw new RuntimeException('No se pudo abrir el archivo de solicitudes: '.$this->ruta);
        }

        try {
            if (! flock($archivo, LOCK_EX)) {
                throw new RuntimeException('No se pudo bloquear el archivo de solicitudes: '.$this->ruta);
            }

            $todas = $this->decodificar(stream_get_contents($archivo));
            $todas[$solicitud->codigo] = $solicitud->toArray();
            $json = json_encode($todas, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);

            ftruncate($archivo, 0);
            rewind($archivo);
            if (fwrite($archivo, $json) === false) {
                throw new RuntimeException('No se pudo guardar el archivo de solicitudes: '.$this->ruta);
            }
            fflush($archivo);
        } finally {
            flock($archivo, LOCK_UN);
            fclose($archivo);
        }
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

    public function todas(): array
    {
        return array_values(array_map(fn (array $datos) => Solicitud::fromArray($datos), $this->leerTodas()));
    }

    private function leerTodas(): array
    {
        if (! is_file($this->ruta)) {
            return [];
        }

        $archivo = fopen($this->ruta, 'r');
        if ($archivo === false) {
            return [];
        }

        try {
            flock($archivo, LOCK_SH);

            return $this->decodificar(stream_get_contents($archivo));
        } finally {
            flock($archivo, LOCK_UN);
            fclose($archivo);
        }
    }

    private function decodificar(string|false $contenido): array
    {
        if ($contenido === false || trim($contenido) === '') {
            return [];
        }

        $datos = json_decode($contenido, true);

        if (! is_array($datos)) {
            throw new RuntimeException('El archivo de solicitudes está dañado: '.$this->ruta);
        }

        return $datos;
    }

    private function crearCarpeta(): void
    {
        $directorio = dirname($this->ruta);

        if (! is_dir($directorio) && ! mkdir($directorio, 0775, true) && ! is_dir($directorio)) {
            throw new RuntimeException('No se pudo crear la carpeta de datos: '.$directorio);
        }
    }
}
