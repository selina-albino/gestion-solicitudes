<?php

namespace App\Solicitudes;

final class Solicitud
{
    /**
     * @param  list<array{fecha: string, accion: string, detalle: string, estado_anterior: ?string, estado_nuevo: string}>  $historial
     */
    public function __construct(
        public readonly string $codigo,
        public readonly string $solicitante,
        public readonly Categoria $categoria,
        public readonly string $descripcion,
        public readonly Prioridad $prioridad,
        public Estado $estado,
        public ?string $tecnico,
        public readonly string $creadaEn,
        public array $historial = [],
    ) {}

    public function registrarCambio(string $fecha, string $accion, string $detalle, ?Estado $anterior): void
    {
        $this->historial[] = [
            'fecha' => $fecha,
            'accion' => $accion,
            'detalle' => $detalle,
            'estado_anterior' => $anterior?->value,
            'estado_nuevo' => $this->estado->value,
        ];
    }

    public function toArray(): array
    {
        return [
            'codigo' => $this->codigo,
            'solicitante' => $this->solicitante,
            'categoria' => $this->categoria->value,
            'descripcion' => $this->descripcion,
            'prioridad' => $this->prioridad->value,
            'estado' => $this->estado->value,
            'tecnico' => $this->tecnico,
            'creada_en' => $this->creadaEn,
            'historial' => $this->historial,
        ];
    }

    public static function fromArray(array $datos): self
    {
        return new self(
            codigo: $datos['codigo'],
            solicitante: $datos['solicitante'],
            categoria: Categoria::from($datos['categoria']),
            descripcion: $datos['descripcion'],
            prioridad: Prioridad::from($datos['prioridad']),
            estado: Estado::from($datos['estado']),
            tecnico: $datos['tecnico'] ?? null,
            creadaEn: $datos['creada_en'],
            historial: $datos['historial'] ?? [],
        );
    }
}
