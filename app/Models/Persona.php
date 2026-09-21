<?php

namespace App\Models;

use Database\Factories\PersonaFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Persona extends Model
{
    /** @use HasFactory<PersonaFactory> */
    use HasFactory;

    protected $fillable = ['nombre', 'activa'];

    protected function casts(): array
    {
        return ['activa' => 'boolean'];
    }

    public function incidentes(): HasMany
    {
        return $this->hasMany(Incidente::class, 'responsable_id');
    }
}
