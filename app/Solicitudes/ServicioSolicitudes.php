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

    private const LARGO_MAXIMO_COMENTARIO = 255;

    private Closure $reloj;

    private PoliticaVencimiento $vencimiento;

    /** @param  list<string>  $tecnicos */
    public function __construct(
        private readonly RepositorioSolicitudes $repositorio,
        private readonly GeneradorCodigo $generador,
        private readonly array $tecnicos,
        ?Closure $reloj = null,
        ?PoliticaVencimiento $vencimiento = null,
    ) {
        $this->reloj = $reloj ?? fn () => new DateTimeImmutable;
        $this->vencimiento = $vencimiento ?? new PoliticaVencimiento;
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
        $solicitud = $this->repositorio->buscar($codigo) ?? throw new SolicitudNoEncontrada($codigo);

        // RF9: se revisa en cada consulta (y antes de asignar o cambiar estado),
        // así la marca es correcta aunque la tarea programada no se haya ejecutado.
        $this->aplicarVencimiento($solicitud);

        return $solicitud;
    }

    /**
     * RF9 · Revisa todas las solicitudes y marca las críticas que pasaron el plazo.
     * La ejecuta el comando programado `solicitudes:marcar-vencidas`.
     *
     * @return list<Solicitud> las que se marcaron en esta ejecución
     */
    public function marcarVencidas(): array
    {
        return array_values(array_filter(
            $this->repositorio->todas(),
            fn (Solicitud $solicitud) => $this->aplicarVencimiento($solicitud),
        ));
    }

    /** RF9 · Hasta cuándo se puede empezar a atender (null si la regla no aplica). */
    public function limiteAtencion(Solicitud $solicitud): ?DateTimeImmutable
    {
        return $this->vencimiento->limite($solicitud)?->setTimezone($this->ahora()->getTimezone());
    }

    public function horasLimiteAtencion(): int
    {
        return $this->vencimiento->horas();
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

        // D-03: el límite del comentario se valida también aquí, no solo en el formulario.
        $comentario = trim((string) $comentario);
        if (mb_strlen($comentario) > self::LARGO_MAXIMO_COMENTARIO) {
            throw new DatosInvalidos(['comentario' => Mensajes::de('comentario', 'max')]);
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

        // RF9: la primera vez que llega a En proceso queda registrada la hora de atención.
        if ($nuevo === Estado::EnProceso && $solicitud->atendidaEn === null) {
            $solicitud->atendidaEn = $this->ahora()->format(DATE_ATOM);
        }

        $detalle = "Estado cambiado de {$anterior->etiqueta()} a {$nuevo->etiqueta()}.";
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

    /** Marca la solicitud como vencida si corresponde. Devuelve true si la marcó ahora. */
    private function aplicarVencimiento(Solicitud $solicitud): bool
    {
        $ahora = $this->ahora();

        if (! $this->vencimiento->debeVencer($solicitud, $ahora)) {
            return false;
        }

        $limite = $this->limiteAtencion($solicitud);
        $solicitud->vencidaEn = $limite->format(DATE_ATOM);
        $solicitud->registrarCambio(
            $ahora->format(DATE_ATOM),
            'Vencimiento',
            "Solicitud {$solicitud->prioridad->etiqueta()} vencida: no fue atendida dentro de "
                ."{$this->vencimiento->horas()} horas (plazo hasta {$limite->format('d/m/Y H:i')}).",
            $solicitud->estado,
        );
        $this->repositorio->guardar($solicitud);

        return true;
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
