<?php

namespace App\Solicitudes;

use App\Solicitudes\Excepciones\DatosInvalidos;
use App\Solicitudes\Excepciones\OperacionNoPermitida;
use App\Solicitudes\Excepciones\SolicitudNoEncontrada;
use Closure;
use DateTimeImmutable;
use RuntimeException;

/**
 * Casos de uso del incremento: registrar, consultar, asignar técnico y cambiar estado.
 * No depende de Laravel, por eso se prueba con pruebas unitarias puras.
 */
final class ServicioSolicitudes
{
    private const INTENTOS_CODIGO = 10;

    private Closure $reloj;

    /** @param  list<string>  $tecnicos */
    public function __construct(
        private readonly RepositorioSolicitudes $repositorio,
        private readonly GeneradorCodigo $generador,
        private readonly array $tecnicos,
        ?Closure $reloj = null,
    ) {
        $this->reloj = $reloj ?? fn () => new DateTimeImmutable;
    }

    /** @param  array<string, mixed>  $datos */
    public function registrar(array $datos): Solicitud
    {
        $this->validarRegistro($datos);

        $ahora = $this->ahora();
        $solicitud = new Solicitud(
            codigo: $this->nuevoCodigo($ahora),
            solicitante: trim($datos['solicitante']),
            categoria: Categoria::from($datos['categoria']),
            descripcion: trim($datos['descripcion']),
            prioridad: Prioridad::from($datos['prioridad']),
            estado: Estado::Registrada,
            tecnico: null,
            creadaEn: $ahora->format(DATE_ATOM),
        );

        $solicitud->registrarCambio(
            $solicitud->creadaEn,
            'Registro',
            "Solicitud registrada por {$solicitud->solicitante} con prioridad {$solicitud->prioridad->etiqueta()}.",
            null,
        );

        $this->repositorio->guardar($solicitud);

        return $solicitud;
    }

    public function consultar(string $codigo): Solicitud
    {
        $codigo = GeneradorCodigo::normalizar($codigo);

        return $this->repositorio->buscar($codigo) ?? throw new SolicitudNoEncontrada($codigo);
    }

    public function asignarTecnico(string $codigo, string $tecnico): Solicitud
    {
        $tecnico = trim($tecnico);

        if ($tecnico === '') {
            throw new DatosInvalidos(['tecnico' => Mensajes::de('tecnico', 'required')]);
        }

        if (! in_array($tecnico, $this->tecnicos, true)) {
            throw new DatosInvalidos(['tecnico' => Mensajes::de('tecnico', 'in')]);
        }

        $solicitud = $this->consultar($codigo);

        if (! $solicitud->estado->aceptaTecnico()) {
            throw new OperacionNoPermitida(
                "No se puede asignar un técnico a una solicitud en estado {$solicitud->estado->etiqueta()}."
            );
        }

        if ($solicitud->tecnico === $tecnico) {
            throw new OperacionNoPermitida("{$tecnico} ya está asignado a esta solicitud.");
        }

        $anterior = $solicitud->estado;
        $detalle = $solicitud->tecnico === null
            ? "Técnico asignado: {$tecnico}."
            : "Técnico cambiado de {$solicitud->tecnico} a {$tecnico}.";

        $solicitud->tecnico = $tecnico;

        if ($solicitud->estado === Estado::Registrada) {
            $solicitud->estado = Estado::Asignada;
        }

        $solicitud->registrarCambio($this->ahora()->format(DATE_ATOM), 'Asignación de técnico', $detalle, $anterior);
        $this->repositorio->guardar($solicitud);

        return $solicitud;
    }

    public function cambiarEstado(string $codigo, Estado|string $nuevo, ?string $comentario = null): Solicitud
    {
        if (is_string($nuevo)) {
            $nuevo = Estado::tryFrom($nuevo)
                ?? throw new DatosInvalidos(['estado' => Mensajes::de('estado', 'in')]);
        }

        $solicitud = $this->consultar($codigo);
        $anterior = $solicitud->estado;

        if ($anterior === $nuevo) {
            throw new OperacionNoPermitida("La solicitud ya está en estado {$nuevo->etiqueta()}.");
        }

        if (! $anterior->puedeCambiarA($nuevo)) {
            throw new OperacionNoPermitida($this->explicarTransicionInvalida($anterior, $nuevo));
        }

        $solicitud->estado = $nuevo;

        $detalle = "Estado cambiado de {$anterior->etiqueta()} a {$nuevo->etiqueta()}.";
        $comentario = trim((string) $comentario);
        if ($comentario !== '') {
            $detalle .= " Comentario: {$comentario}";
        }

        $solicitud->registrarCambio($this->ahora()->format(DATE_ATOM), 'Cambio de estado', $detalle, $anterior);
        $this->repositorio->guardar($solicitud);

        return $solicitud;
    }

    /** @return list<string> */
    public function tecnicosDisponibles(): array
    {
        return $this->tecnicos;
    }

    /** @param  array<string, mixed>  $datos */
    private function validarRegistro(array $datos): void
    {
        $errores = [];

        $solicitante = trim((string) ($datos['solicitante'] ?? ''));
        if ($solicitante === '') {
            $errores['solicitante'] = Mensajes::de('solicitante', 'required');
        } elseif (mb_strlen($solicitante) < 3) {
            $errores['solicitante'] = Mensajes::de('solicitante', 'min');
        } elseif (mb_strlen($solicitante) > 100) {
            $errores['solicitante'] = Mensajes::de('solicitante', 'max');
        }

        $categoria = (string) ($datos['categoria'] ?? '');
        if ($categoria === '') {
            $errores['categoria'] = Mensajes::de('categoria', 'required');
        } elseif (Categoria::tryFrom($categoria) === null) {
            $errores['categoria'] = Mensajes::de('categoria', 'in');
        }

        $descripcion = trim((string) ($datos['descripcion'] ?? ''));
        if ($descripcion === '') {
            $errores['descripcion'] = Mensajes::de('descripcion', 'required');
        } elseif (mb_strlen($descripcion) < 10) {
            $errores['descripcion'] = Mensajes::de('descripcion', 'min');
        } elseif (mb_strlen($descripcion) > 1000) {
            $errores['descripcion'] = Mensajes::de('descripcion', 'max');
        }

        $prioridad = (string) ($datos['prioridad'] ?? '');
        if ($prioridad === '') {
            $errores['prioridad'] = Mensajes::de('prioridad', 'required');
        } elseif (Prioridad::tryFrom($prioridad) === null) {
            $errores['prioridad'] = Mensajes::de('prioridad', 'in');
        }

        if ($errores !== []) {
            throw new DatosInvalidos($errores);
        }
    }

    private function nuevoCodigo(DateTimeImmutable $fecha): string
    {
        for ($i = 0; $i < self::INTENTOS_CODIGO; $i++) {
            $codigo = $this->generador->generar($fecha);

            if (! $this->repositorio->existe($codigo)) {
                return $codigo;
            }
        }

        throw new RuntimeException('No se pudo generar un código único. Intente nuevamente.');
    }

    private function explicarTransicionInvalida(Estado $actual, Estado $nuevo): string
    {
        $permitidos = $actual->siguientesPermitidos();

        if ($permitidos === []) {
            return "La solicitud está {$actual->etiqueta()} y ya no admite cambios de estado.";
        }

        if ($nuevo === Estado::Asignada) {
            return 'Para pasar a Asignada debe asignar un técnico.';
        }

        $opciones = implode(', ', array_map(fn (Estado $e) => $e->etiqueta(), $permitidos));

        return "No se puede pasar de {$actual->etiqueta()} a {$nuevo->etiqueta()}. Desde {$actual->etiqueta()} solo se puede pasar a: {$opciones}.";
    }

    private function ahora(): DateTimeImmutable
    {
        return ($this->reloj)();
    }
}
