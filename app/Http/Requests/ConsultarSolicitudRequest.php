<?php

namespace App\Http\Requests;

use App\Solicitudes\GeneradorCodigo;
use App\Solicitudes\Mensajes;
use Illuminate\Foundation\Http\FormRequest;

class ConsultarSolicitudRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** Acepta el código en minúsculas o con espacios alrededor. */
    protected function prepareForValidation(): void
    {
        if (is_string($this->input('codigo'))) {
            $this->merge(['codigo' => GeneradorCodigo::normalizar($this->input('codigo'))]);
        }
    }

    public function rules(): array
    {
        return [
            'codigo' => ['required', 'regex:'.GeneradorCodigo::PATRON],
        ];
    }

    public function messages(): array
    {
        return Mensajes::CAMPOS;
    }

    protected function getRedirectUrl(): string
    {
        return route('solicitudes.buscar');
    }
}
