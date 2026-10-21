<?php

namespace App\Http\Requests\Api;

use App\Actions\GuardarIncidente;
use App\Models\Incidente;
use Illuminate\Foundation\Http\FormRequest;

class GuardarIncidenteRequest extends FormRequest
{
    public function authorize(): bool
    {
        $incidente = $this->route('incidente');

        return $incidente
            ? $this->user()->can('update', $incidente)
            : $this->user()->can('create', Incidente::class);
    }

    public function rules(): array
    {
        return GuardarIncidente::reglas(parcial: $this->isMethod('PATCH'));
    }

    public function messages(): array
    {
        return GuardarIncidente::mensajes();
    }
}
