<?php

namespace App\Solicitudes;

use DateTimeImmutable;

/**
 * Genera códigos con el formato SOL-AAAAMMDD-XXXXXX.
 * El sufijo evita caracteres confusos (0/O, 1/I/L) para que se puedan dictar por teléfono.
 */
final class GeneradorCodigo
{
    public const PATRON = '/^SOL-\d{8}-[A-HJKMNP-Z2-9]{6}$/';

    private const ALFABETO = 'ABCDEFGHJKMNPQRSTUVWXYZ23456789';

    private const LARGO_SUFIJO = 6;

    public function generar(DateTimeImmutable $fecha): string
    {
        $sufijo = '';
        $ultimo = strlen(self::ALFABETO) - 1;

        for ($i = 0; $i < self::LARGO_SUFIJO; $i++) {
            $sufijo .= self::ALFABETO[random_int(0, $ultimo)];
        }

        return 'SOL-'.$fecha->format('Ymd').'-'.$sufijo;
    }

    public static function esValido(string $codigo): bool
    {
        return preg_match(self::PATRON, $codigo) === 1;
    }

    public static function normalizar(string $codigo): string
    {
        return strtoupper(trim($codigo));
    }
}
