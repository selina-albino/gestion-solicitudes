<?php

namespace App\Solicitudes;

enum Categoria: string
{
    case Hardware = 'hardware';
    case Software = 'software';
    case Redes = 'redes';
    case Accesos = 'accesos';
    case Otro = 'otro';

    public function etiqueta(): string
    {
        return match ($this) {
            self::Hardware => 'Hardware',
            self::Software => 'Software',
            self::Redes => 'Redes e internet',
            self::Accesos => 'Cuentas y accesos',
            self::Otro => 'Otro',
        };
    }
}
