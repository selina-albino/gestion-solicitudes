<?php

namespace App\Http\Requests;

use App\Solicitudes\Categoria;
use App\Solicitudes\Mensajes;
use App\Solicitudes\Prioridad;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegistrarSolicitudRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'solicitante' => ['required', 'string', 'min:3', 'max:100'],
            // Rule::in (y no Rule::enum) para que el mensaje personalizado "campo.in" se aplique.
            'categoria' => ['required', Rule::in(array_column(Categoria::cases(), 'value'))],
            'descripcion' => ['required', 'string', 'min:10', 'max:1000'],
            'prioridad' => ['required', Rule::in(array_column(Prioridad::cases(), 'value'))],
        ];
    }

    public function messages(): array
    {
        return Mensajes::CAMPOS;
    }
}
