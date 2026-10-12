<?php

namespace App\Solicitudes\Excepciones;

use DomainException;

final class SolicitudNoEncontrada extends DomainException
{
    public function __construct(public readonly string $codigo)
    {
        parent::__construct("No existe ninguna solicitud con el código {$codigo}. Revise que esté bien escrito.");
    }
}
