<?php

namespace App\Livewire\Incidentes;

use App\Models\Incidente;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithPagination;

class ListadoIncidentes extends Component
{
    use AuthorizesRequests;
    use WithPagination;

    public function mount(): void
    {
        $this->authorize('viewAny', Incidente::class);
    }

    public function render(): View
    {
        return view('livewire.incidentes.listado-incidentes', [
            'incidentes' => Incidente::query()
                ->with('responsable')
                ->orderByDesc('id')
                ->paginate(5),
        ])->layout('layouts.app', ['titulo' => 'Incidentes']);
    }
}
