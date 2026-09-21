<?php

namespace Database\Seeders;

use App\Enums\PrioridadIncidente;
use App\Models\Incidente;
use App\Models\Persona;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class EvaluacionSeeder extends Seeder
{
    public function run(): void
    {
        $editor = User::query()->updateOrCreate(
            ['email' => 'editor@mesa.test'],
            ['name' => 'Elena Editora', 'password' => Hash::make('Evaluacion2026!'), 'puede_editar' => true],
        );

        User::query()->updateOrCreate(
            ['email' => 'lector@mesa.test'],
            ['name' => 'Leonardo Lector', 'password' => Hash::make('Evaluacion2026!'), 'puede_editar' => false],
        );

        $personas = collect(['Ana Torres', 'Bruno Díaz', 'Carolina Méndez'])
            ->map(fn (string $nombre) => Persona::query()->updateOrCreate(['nombre' => $nombre], ['activa' => true]));

        $prioridades = PrioridadIncidente::cases();
        $estados = ['abierto', 'en_proceso', 'resuelto'];

        foreach (range(1, 16) as $numero) {
            Incidente::query()->updateOrCreate(
                ['codigo' => sprintf('INC-2026-%03d', $numero)],
                [
                    'titulo' => $numero === 1
                        ? 'Intermitencia prolongada en el proceso nocturno de conciliación de operaciones'
                        : "Incidente ficticio {$numero}",
                    'descripcion' => "Descripción controlada del incidente ficticio {$numero} para la evaluación.",
                    'estado' => $estados[($numero - 1) % count($estados)],
                    'prioridad' => $prioridades[($numero - 1) % count($prioridades)],
                    'responsable_id' => $personas[($numero - 1) % $personas->count()]->id,
                ],
            );
        }
    }
}
