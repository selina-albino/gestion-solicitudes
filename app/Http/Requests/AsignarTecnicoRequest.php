<?php

namespace App\Http\Requests;

use App\Solicitudes\Mensajes;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AsignarTecnicoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tecnico' => ['required', Rule::in(config('solicitudes.tecnicos', []))],
        ];
    }

    public function messages(): array
    {
        return Mensajes::CAMPOS;
    }
}
