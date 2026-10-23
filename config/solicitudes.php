<?php

return [

    /*
    | Archivo JSON donde se guardan las solicitudes (dentro de storage/app).
    | Las pruebas lo reemplazan por un archivo temporal.
    */
    'archivo' => storage_path('app/'.env('SOLICITUDES_ARCHIVO', 'solicitudes.json')),

    /*
    | Técnicos disponibles para asignar. En un incremento posterior
    | saldrán de la base de datos.
    */
    'tecnicos' => [
        'Ana Quiroga',
        'Bruno Mendoza',
        'Carla Rojas',
        'Diego Vargas',
    ],

    /*
    | RF9 · Vencimiento: las solicitudes de esta prioridad que no pasen a
    | "En proceso" dentro de estas horas quedan marcadas como vencidas.
    | Son horas corridas (no se descuentan noches ni feriados).
    */
    'vencimiento' => [
        'prioridad' => 'critica',
        'horas' => (int) env('SOLICITUDES_HORAS_VENCIMIENTO', 4),
    ],

];
