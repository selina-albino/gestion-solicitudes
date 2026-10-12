<?php

namespace App\Solicitudes\Excepciones;

use DomainException;

/** Datos de entrada incompletos o incorrectos. Trae un mensaje por campo. */
final class DatosInvalidos extends DomainException
{
    /** @param  array<string, string>  $errores */
    public function __construct(public readonly array $errores)
    {
        parent::__construct(implode(' ', $errores));
    }
}
