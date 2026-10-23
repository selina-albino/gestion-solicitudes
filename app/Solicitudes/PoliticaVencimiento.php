<?php

namespace App\Solicitudes;

use DateTimeImmutable;

/**
 * RF9 · Regla de vencimiento: una solicitud de la prioridad indicada (Crítica)
 * que no fue atendida dentro del plazo (4 horas) queda marcada como vencida.
 *
 * "Atendida" significa que llegó a En proceso (o salió del flujo por cancelación).
 * Mientras siga en Registrada o Asignada, el reloj corre: asignar un técnico
 * no es lo mismo que empezar a atender.
 */
final class PoliticaVencimiento
{
    public function __construct(
        private readonly int $horas = 4,
        private readonly Prioridad $prioridad = Prioridad::Critica,
    ) {}

    public function horas(): int
    {
        return $this->horas;
    }

    public function aplicaA(Solicitud $solicitud): bool
    {
        return $solicitud->prioridad === $this->prioridad;
    }

    /** Fecha y hora límite para empezar a atender, o null si la regla no aplica. */
    public function limite(Solicitud $solicitud): ?DateTimeImmutable
    {
        if (! $this->aplicaA($solicitud)) {
            return null;
        }

        return (new DateTimeImmutable($solicitud->creadaEn))->modify("+{$this->horas} hours");
    }

    /**
     * Vence si: aplica la regla, aún no estaba vencida, sigue esperando atención
     * y ya pasó el límite. Justo en el límite (4:00:00) todavía está a tiempo.
     */
    public function debeVencer(Solicitud $solicitud, DateTimeImmutable $ahora): bool
    {
        $limite = $this->limite($solicitud);

        return $limite !== null
            && ! $solicitud->estaVencida()
            && $solicitud->estado->esperaAtencion()
            && $ahora > $limite;
    }
}
