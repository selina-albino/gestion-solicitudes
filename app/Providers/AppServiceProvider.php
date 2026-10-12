<?php

namespace App\Providers;

use App\Solicitudes\GeneradorCodigo;
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
            ),
        );
    }

    public function boot(): void
    {
        //
    }
}
