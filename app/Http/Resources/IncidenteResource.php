<?php

namespace App\Http\Resources;

use App\Models\Incidente;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Incidente */
class IncidenteResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'codigo' => $this->codigo,
            'titulo' => $this->titulo,
            'descripcion' => $this->descripcion,
            'estado' => $this->estado,
            'prioridad' => $this->prioridad->value,
            'prioridad_etiqueta' => $this->prioridad->etiqueta(),
            'responsable' => $this->whenLoaded('responsable', fn () => [
                'id' => $this->responsable->id,
                'nombre' => $this->responsable->nombre,
            ]),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
