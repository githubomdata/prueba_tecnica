<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class ListarIncidentesRequest extends FormRequest
{
    public function authorize(): bool
    {
        // TODO: Completar la autorización.
        return false;
    }

    public function rules(): array
    {
        // TODO: Completar las reglas de validación.
        return [];
    }
}
