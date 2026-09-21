<?php

namespace App\Livewire\Incidentes;

use App\Actions\GuardarIncidente;
use App\Models\Incidente;
use App\Models\Persona;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\View\View;
use Livewire\Component;

class FormularioIncidente extends Component
{
    use AuthorizesRequests;

    public ?int $incidenteId = null;

    public string $titulo = '';

    public string $descripcion = '';

    public string $estado = 'abierto';

    public ?int $responsable_id = null;

    public function mount(?int $incidenteId = null): void
    {
        if ($incidenteId === null) {
            $this->authorize('create', Incidente::class);

            return;
        }

        $incidente = Incidente::findOrFail($incidenteId);
        $this->authorize('update', $incidente);

        $this->incidenteId = $incidente->id;
        $this->titulo = $incidente->titulo;
        $this->descripcion = $incidente->descripcion;
        $this->estado = $incidente->estado;
        $this->responsable_id = $incidente->responsable_id;
    }

    public function guardar(GuardarIncidente $guardarIncidente): void
    {
        $incidente = $this->incidenteId
            ? Incidente::findOrFail($this->incidenteId)
            : null;

        $guardado = $guardarIncidente->ejecutar(auth()->user(), [
            'titulo' => $this->titulo,
            'descripcion' => $this->descripcion,
            'estado' => $this->estado,
            'responsable_id' => $this->responsable_id,
        ], $incidente);

        session()->flash('mensaje', $incidente ? 'Incidente actualizado.' : 'Incidente creado.');
        $this->redirectRoute('incidentes.index', navigate: true);
    }

    public function render(): View
    {
        return view('livewire.incidentes.formulario-incidente', [
            'personas' => Persona::query()->where('activa', true)->orderBy('nombre')->get(),
        ])->layout('layouts.app', [
            'titulo' => $this->incidenteId ? 'Editar incidente' : 'Nuevo incidente',
        ]);
    }
}
