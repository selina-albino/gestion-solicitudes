<?php

namespace App\Solicitudes;

use DateTimeImmutable;

/**
 * Indicadores de seguimiento calculados a partir de las solicitudes guardadas.
 * Se basan en el historial, así que también sirven para datos de la versión 1.0.
 */
final class Indicadores
{
    /** @param  list<Solicitud>  $solicitudes */
    public function __construct(private readonly array $solicitudes) {}

    /** @return array<string, int> estado (etiqueta) => cantidad, solo estados abiertos */
    public function abiertasPorEstado(): array
    {
        $conteo = [];
        foreach (Estado::cases() as $estado) {
            if ($estado->siguientesPermitidos() !== []) {
                $conteo[$estado->etiqueta()] = 0;
            }
        }

        foreach ($this->solicitudes as $solicitud) {
            if (isset($conteo[$solicitud->estado->etiqueta()])) {
                $conteo[$solicitud->estado->etiqueta()]++;
            }
        }

        return $conteo;
    }

    /** Minutos promedio entre el registro y la primera vez que pasó a En proceso (null si ninguna). */
    public function minutosPromedioRespuesta(?Prioridad $prioridad = null): ?float
    {
        $minutos = [];
        foreach ($this->solicitudes as $solicitud) {
            if ($prioridad !== null && $solicitud->prioridad !== $prioridad) {
                continue;
            }
            $atencion = $this->primeraVez($solicitud, Estado::EnProceso);
            if ($atencion !== null) {
                $inicio = new DateTimeImmutable($solicitud->creadaEn);
                $minutos[] = ($atencion->getTimestamp() - $inicio->getTimestamp()) / 60;
            }
        }

        return $minutos === [] ? null : array_sum($minutos) / count($minutos);
    }

    /** Solicitudes que volvieron de Resuelta a En proceso al menos una vez. */
    public function reabiertas(): int
    {
        return count(array_filter($this->solicitudes, function (Solicitud $solicitud) {
            foreach ($solicitud->historial as $cambio) {
                if ($cambio['estado_anterior'] === Estado::Resuelta->value && $cambio['estado_nuevo'] === Estado::EnProceso->value) {
                    return true;
                }
            }

            return false;
        }));
    }

    /** @return array{criticas: int, vencidas: int, cumplimiento: ?float} */
    public function cumplimientoCriticas(): array
    {
        $criticas = array_filter($this->solicitudes, fn (Solicitud $s) => $s->prioridad === Prioridad::Critica);
        $vencidas = count(array_filter($criticas, fn (Solicitud $s) => $s->estaVencida()));
        $total = count($criticas);

        return [
            'criticas' => $total,
            'vencidas' => $vencidas,
            'cumplimiento' => $total === 0 ? null : 100.0 * ($total - $vencidas) / $total,
        ];
    }

    private function primeraVez(Solicitud $solicitud, Estado $estado): ?DateTimeImmutable
    {
        foreach ($solicitud->historial as $cambio) {
            if ($cambio['estado_nuevo'] === $estado->value && $cambio['estado_anterior'] !== $estado->value) {
                return new DateTimeImmutable($cambio['fecha']);
            }
        }

        return null;
    }
}
