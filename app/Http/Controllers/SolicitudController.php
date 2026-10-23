<?php

namespace App\Http\Controllers;

use App\Http\Requests\AsignarTecnicoRequest;
use App\Http\Requests\CambiarEstadoRequest;
use App\Http\Requests\ConsultarSolicitudRequest;
use App\Http\Requests\RegistrarSolicitudRequest;
use App\Solicitudes\Categoria;
use App\Solicitudes\Excepciones\DatosInvalidos;
use App\Solicitudes\Excepciones\OperacionNoPermitida;
use App\Solicitudes\Excepciones\SolicitudNoEncontrada;
use App\Solicitudes\Prioridad;
use App\Solicitudes\ServicioSolicitudes;
use Illuminate\Http\RedirectResponse;
use Illuminate\Contracts\View\View;

class SolicitudController extends Controller
{
    public function __construct(private readonly ServicioSolicitudes $servicio) {}

    public function create(): View
    {
        return view('solicitudes.create', [
            'categorias' => Categoria::cases(),
            'prioridades' => Prioridad::cases(),
        ]);
    }

    public function store(RegistrarSolicitudRequest $request): RedirectResponse
    {
        try {
            $solicitud = $this->servicio->registrar($request->validated());
        } catch (DatosInvalidos $e) {
            return back()->withErrors($e->errores)->withInput();
        }

        return redirect()
            ->route('solicitudes.show', $solicitud->codigo)
            ->with('exito', "Solicitud registrada. Guarde este código para consultarla: {$solicitud->codigo}");
    }

    public function buscar(): View
    {
        return view('solicitudes.consultar');
    }

    public function consultar(ConsultarSolicitudRequest $request): RedirectResponse
    {
        try {
            $solicitud = $this->servicio->consultar($request->validated('codigo'));
        } catch (SolicitudNoEncontrada $e) {
            return $this->volverABuscar($e);
        }

        return redirect()->route('solicitudes.show', $solicitud->codigo);
    }

    public function show(string $codigo): View|RedirectResponse
    {
        try {
            $solicitud = $this->servicio->consultar($codigo);
        } catch (SolicitudNoEncontrada $e) {
            return $this->volverABuscar($e);
        }

        return view('solicitudes.show', [
            'solicitud' => $solicitud,
            'tecnicos' => $this->servicio->tecnicosDisponibles(),
            'limiteAtencion' => $this->servicio->limiteAtencion($solicitud),
            'horasLimite' => $this->servicio->horasLimiteAtencion(),
        ]);
    }

    public function asignarTecnico(AsignarTecnicoRequest $request, string $codigo): RedirectResponse
    {
        try {
            $solicitud = $this->servicio->asignarTecnico($codigo, $request->validated('tecnico'));
        } catch (SolicitudNoEncontrada $e) {
            return $this->volverABuscar($e);
        } catch (DatosInvalidos|OperacionNoPermitida $e) {
            return back()->withErrors(['tecnico' => $e->getMessage()]);
        }

        return redirect()
            ->route('solicitudes.show', $solicitud->codigo)
            ->with('exito', "Técnico asignado: {$solicitud->tecnico}.");
    }

    public function cambiarEstado(CambiarEstadoRequest $request, string $codigo): RedirectResponse
    {
        try {
            $solicitud = $this->servicio->cambiarEstado(
                $codigo,
                $request->validated('estado'),
                $request->validated('comentario'),
            );
        } catch (SolicitudNoEncontrada $e) {
            return $this->volverABuscar($e);
        } catch (DatosInvalidos|OperacionNoPermitida $e) {
            return back()->withErrors(['estado' => $e->getMessage()])->withInput();
        }

        return redirect()
            ->route('solicitudes.show', $solicitud->codigo)
            ->with('exito', "Estado actualizado a {$solicitud->estado->etiqueta()}.");
    }

    private function volverABuscar(SolicitudNoEncontrada $e): RedirectResponse
    {
        return redirect()
            ->route('solicitudes.buscar')
            ->withErrors(['codigo' => $e->getMessage()])
            ->withInput(['codigo' => $e->codigo]);
    }
}
