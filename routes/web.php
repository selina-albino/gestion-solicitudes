<?php

use App\Http\Controllers\SolicitudController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/solicitudes/nueva');

Route::controller(SolicitudController::class)
    ->prefix('solicitudes')
    ->name('solicitudes.')
    ->group(function () {
        // Rutas fijas antes de {codigo} para que no se confundan con un código.
        Route::get('/nueva', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('/consultar', 'buscar')->name('buscar');
        Route::get('/consulta', 'consultar')->name('consultar');

        Route::get('/{codigo}', 'show')->name('show');
        Route::patch('/{codigo}/tecnico', 'asignarTecnico')->name('tecnico');
        Route::patch('/{codigo}/estado', 'cambiarEstado')->name('estado');
    });
