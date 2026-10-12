<?php

namespace App\Http\Requests;

use App\Solicitudes\Estado;
use App\Solicitudes\Mensajes;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CambiarEstadoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'estado' => ['required', Rule::in(array_column(Estado::cases(), 'value'))],
            'comentario' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return Mensajes::CAMPOS;
    }
}
