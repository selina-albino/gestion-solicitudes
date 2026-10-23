<?php

namespace App\Solicitudes;

/**
 * Mensajes de validación en un solo lugar: los usan tanto los Form Requests
 * (capa web) como el servicio (capa de dominio), así nunca se contradicen.
 */
final class Mensajes
{
    public const CAMPOS = [
        'solicitante.required' => 'Ingrese el nombre de quien hace la solicitud.',
        'solicitante.string' => 'El nombre del solicitante debe ser texto.',
        'solicitante.min' => 'El nombre del solicitante debe tener al menos 3 caracteres.',
        'solicitante.max' => 'El nombre del solicitante no puede superar los 100 caracteres.',
        'categoria.required' => 'Seleccione una categoría.',
        'categoria.in' => 'La categoría elegida no es válida. Elija una de la lista.',
        'descripcion.required' => 'Describa el problema o pedido.',
        'descripcion.string' => 'La descripción debe ser texto.',
        'descripcion.min' => 'La descripción debe tener al menos 10 caracteres para poder entender el pedido.',
        'descripcion.max' => 'La descripción no puede superar los 1000 caracteres.',
        'prioridad.required' => 'Seleccione una prioridad.',
        'prioridad.in' => 'La prioridad elegida no es válida. Elija una de la lista.',
        'tecnico.required' => 'Seleccione el técnico que atenderá la solicitud.',
        'tecnico.in' => 'El técnico elegido no está en la lista de técnicos disponibles.',
        'estado.required' => 'Seleccione el nuevo estado.',
        'estado.in' => 'El estado elegido no es válido.',
        'comentario.string' => 'El comentario debe ser texto.',
        'comentario.max' => 'El comentario no puede superar los 255 caracteres.',
        'codigo.required' => 'Ingrese el código de la solicitud.',
        'codigo.regex' => 'El código no tiene el formato correcto. Ejemplo: SOL-20261011-AB3K7Q.',
    ];

    public static function de(string $campo, string $regla): string
    {
        // D-05: una clave que no existe ya no rompe la página con un error interno.
        return self::CAMPOS["{$campo}.{$regla}"] ?? "El dato \"{$campo}\" no es válido.";
    }
}
