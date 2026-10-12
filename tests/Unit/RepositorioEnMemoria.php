<?php

namespace Tests\Unit;

use App\Solicitudes\RepositorioSolicitudes;
use App\Solicitudes\Solicitud;

/** Doble de prueba: guarda en un arreglo para que las pruebas unitarias no toquen disco. */
final class RepositorioEnMemoria implements RepositorioSolicitudes
{
    /** @var array<string, array> */
    public array $datos = [];

    /** Cuántas veces seguidas "existe" debe responder true (para simular colisiones de código). */
    public int $colisionesSimuladas = 0;

    public function guardar(Solicitud $solicitud): void
    {
        $this->datos[$solicitud->codigo] = $solicitud->toArray();
    }

    public function buscar(string $codigo): ?Solicitud
    {
        return isset($this->datos[$codigo]) ? Solicitud::fromArray($this->datos[$codigo]) : null;
    }

    public function existe(string $codigo): bool
    {
        if ($this->colisionesSimuladas > 0) {
            $this->colisionesSimuladas--;

            return true;
        }

        return isset($this->datos[$codigo]);
    }
}
