<?php

namespace App\Models;

use App\Enums\PrioridadIncidente;
use Database\Factories\IncidenteFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Incidente extends Model
{
    /** @use HasFactory<IncidenteFactory> */
    use HasFactory;

    protected $fillable = [
        'codigo',
        'titulo',
        'descripcion',
        'estado',
        'prioridad',
        'responsable_id',
    ];

    protected function casts(): array
    {
        return ['prioridad' => PrioridadIncidente::class];
    }

    public function responsable(): BelongsTo
    {
        return $this->belongsTo(Persona::class, 'responsable_id');
    }
}
