<?php

namespace App\Providers;

use App\Solicitudes\GeneradorCodigo;
use App\Solicitudes\PoliticaVencimiento;
use App\Solicitudes\Prioridad;
use App\Solicitudes\RepositorioJson;
use App\Solicitudes\RepositorioSolicitudes;
use App\Solicitudes\ServicioSolicitudes;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // La ruta se lee al resolver (no al arrancar) para que las pruebas puedan cambiarla.
        $this->app->bind(
            RepositorioSolicitudes::class,
            fn () => new RepositorioJson(config('solicitudes.archivo')),
        );

        $this->app->bind(
            ServicioSolicitudes::class,
            fn ($app) => new ServicioSolicitudes(
                $app->make(RepositorioSolicitudes::class),
                new GeneradorCodigo,
                config('solicitudes.tecnicos', []),
                // Reloj de Laravel: en las pruebas se puede "adelantar el tiempo" con $this->travel().
                fn () => now()->toImmutable(),
                new PoliticaVencimiento(
                    (int) config('solicitudes.vencimiento.horas', 4),
                    Prioridad::from(config('solicitudes.vencimiento.prioridad', 'critica')),
                ),
            ),
        );
    }

    public function boot(): void
    {
        //
    }
}
