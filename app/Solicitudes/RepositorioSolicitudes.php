<?php

namespace App\Solicitudes;

interface RepositorioSolicitudes
{
    public function guardar(Solicitud $solicitud): void;

    public function buscar(string $codigo): ?Solicitud;

    public function existe(string $codigo): bool;
}
