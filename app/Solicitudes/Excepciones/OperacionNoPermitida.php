<?php

namespace App\Solicitudes\Excepciones;

use DomainException;

/** La operación es válida en forma, pero no en el estado actual de la solicitud. */
final class OperacionNoPermitida extends DomainException {}
