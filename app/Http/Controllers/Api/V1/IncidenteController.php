<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\GuardarIncidente;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\GuardarIncidenteRequest;
use App\Http\Resources\IncidenteResource;
use App\Models\Incidente;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

class IncidenteController extends Controller
{
    // TODO (Fase 2, requerimiento 1): Integrar ListarIncidentesRequest y completar el filtro por prioridad.
    public function index(): AnonymousResourceCollection
    {
        Gate::authorize('viewAny', Incidente::class);

        return IncidenteResource::collection(
            Incidente::query()->with('responsable')->orderByDesc('id')->paginate(5),
        );
    }

    public function show(Incidente $incidente): IncidenteResource
    {
        Gate::authorize('view', $incidente);

        return new IncidenteResource($incidente->load('responsable'));
    }

    public function store(GuardarIncidenteRequest $request, GuardarIncidente $guardarIncidente): JsonResponse
    {
        $incidente = $guardarIncidente->ejecutar($request->user(), $request->validated());

        return (new IncidenteResource($incidente->refresh()->load('responsable')))
            ->response()
            ->setStatusCode(201)
            ->header('Location', route('api.v1.incidentes.show', $incidente));
    }

    public function update(GuardarIncidenteRequest $request, Incidente $incidente, GuardarIncidente $guardarIncidente): IncidenteResource
    {
        $guardado = $guardarIncidente->ejecutar($request->user(), $request->validated(), $incidente);

        return new IncidenteResource($guardado->refresh()->load('responsable'));
    }

    // TODO (Fase 2, requerimiento 2): Añadir el método para actualizar exclusivamente la prioridad,
    // utilizando ActualizarPrioridadIncidenteRequest y el recurso JSON existente.
}
