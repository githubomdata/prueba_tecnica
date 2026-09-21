<?php

namespace Database\Factories;

use App\Enums\PrioridadIncidente;
use App\Models\Persona;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class IncidenteFactory extends Factory
{
    public function definition(): array
    {
        return [
            'codigo' => 'INC-'.Str::upper(Str::random(10)),
            'titulo' => fake()->sentence(5),
            'descripcion' => fake()->paragraph(),
            'estado' => fake()->randomElement(['abierto', 'en_proceso', 'resuelto']),
            'prioridad' => PrioridadIncidente::MEDIA,
            'responsable_id' => Persona::factory(),
        ];
    }
}
