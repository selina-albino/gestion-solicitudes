<?php

namespace App\Solicitudes;

enum Estado: string
{
    case Registrada = 'registrada';
    case Asignada = 'asignada';
    case EnProceso = 'en_proceso';
    case Resuelta = 'resuelta';
    case Cerrada = 'cerrada';
    case Cancelada = 'cancelada';

    public function etiqueta(): string
    {
        return match ($this) {
            self::Registrada => 'Registrada',
            self::Asignada => 'Asignada',
            self::EnProceso => 'En proceso',
            self::Resuelta => 'Resuelta',
            self::Cerrada => 'Cerrada',
            self::Cancelada => 'Cancelada',
        };
    }

    /**
     * Estados a los que se puede pasar manualmente desde el estado actual.
     * "Asignada" no aparece: solo se llega asignando un técnico.
     *
     * @return list<self>
     */
    public function siguientesPermitidos(): array
    {
        return match ($this) {
            self::Registrada => [self::Cancelada],
            self::Asignada => [self::EnProceso, self::Cancelada],
            self::EnProceso => [self::Resuelta, self::Cancelada],
            self::Resuelta => [self::Cerrada, self::EnProceso],
            self::Cerrada, self::Cancelada => [],
        };
    }

    public function puedeCambiarA(self $nuevo): bool
    {
        return in_array($nuevo, $this->siguientesPermitidos(), true);
    }

    /** RF9 · Estados en los que todavía nadie empezó a atender la solicitud. */
    public function esperaAtencion(): bool
    {
        return in_array($this, [self::Registrada, self::Asignada], true);
    }

    public function aceptaTecnico(): bool
    {
        return in_array($this, [self::Registrada, self::Asignada, self::EnProceso], true);
    }
}
