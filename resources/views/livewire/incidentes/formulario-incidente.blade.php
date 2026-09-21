<section>
    <div class="page-heading">
        <div>
            <p class="eyebrow">Gestión</p>
            <h1>{{ $incidenteId ? 'Editar incidente' : 'Nuevo incidente' }}</h1>
        </div>
        <a class="button secondary" href="{{ route('incidentes.index') }}" wire:navigate>Volver</a>
    </div>

    <form wire:submit="guardar" class="card stack">
        <label>
            Título
            <input type="text" wire:model="titulo" maxlength="180">
            @error('titulo') <span class="error">{{ $message }}</span> @enderror
        </label>

        <label>
            Descripción
            <textarea wire:model="descripcion" rows="6" maxlength="5000"></textarea>
            @error('descripcion') <span class="error">{{ $message }}</span> @enderror
        </label>

        <div class="form-grid">
            <label>
                Estado
                <select wire:model="estado">
                    <option value="abierto">Abierto</option>
                    <option value="en_proceso">En proceso</option>
                    <option value="resuelto">Resuelto</option>
                </select>
                @error('estado') <span class="error">{{ $message }}</span> @enderror
            </label>

            <label>
                Responsable
                <select wire:model="responsable_id">
                    <option value="">Seleccione una persona</option>
                    @foreach ($personas as $persona)
                        <option value="{{ $persona->id }}">{{ $persona->nombre }}</option>
                    @endforeach
                </select>
                @error('responsable_id') <span class="error">{{ $message }}</span> @enderror
            </label>
        </div>

        <div>
            <button class="button" type="submit" wire:loading.attr="disabled">
                <span wire:loading.remove>Guardar</span>
                <span wire:loading>Guardando…</span>
            </button>
        </div>
    </form>
</section>
