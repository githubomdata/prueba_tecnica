<?php

namespace App\Actions;

use App\Models\Incidente;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class GuardarIncidente
{
    /**
     * @param  array{titulo?:mixed, descripcion?:mixed, estado?:mixed, responsable_id?:mixed}  $datos
     */
    public function ejecutar(User $usuario, array $datos, ?Incidente $incidente = null): Incidente
    {
        Gate::forUser($usuario)->authorize(
            $incidente ? 'update' : 'create',
            $incidente ?? Incidente::class,
        );

        $validados = Validator::make(
            $datos,
            self::reglas(parcial: $incidente !== null),
            self::mensajes(),
        )->validate();

        $incidente ??= new Incidente(['codigo' => $this->nuevoCodigo()]);
        $incidente->fill($validados);
        $incidente->save();

        return $incidente;
    }

    public static function reglas(bool $parcial = false): array
    {
        $reglas = [
            'titulo' => ['required', 'string', 'max:180'],
            'descripcion' => ['required', 'string', 'min:10', 'max:5000'],
            'estado' => ['required', Rule::in(['abierto', 'en_proceso', 'resuelto'])],
            'responsable_id' => [
                'required',
                'integer',
                Rule::exists('personas', 'id')->where(fn ($query) => $query->where('activa', true)),
            ],
        ];

        if ($parcial) {
            foreach ($reglas as &$regla) {
                array_unshift($regla, 'sometimes');
            }
        }

        return $reglas;
    }

    public static function mensajes(): array
    {
        return ['responsable_id.exists' => 'El responsable seleccionado no existe o no está activo.'];
    }

    private function nuevoCodigo(): string
    {
        do {
            $codigo = 'INC-'.Str::upper(Str::random(10));
        } while (Incidente::where('codigo', $codigo)->exists());

        return $codigo;
    }
}
