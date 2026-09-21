<section>
    <div class="page-heading">
        <div>
            <p class="eyebrow">Mesa de trabajo</p>
            <h1>Incidentes</h1>
        </div>
        @can('create', App\Models\Incidente::class)
            <a class="button" href="{{ route('incidentes.create') }}" wire:navigate>Nuevo incidente</a>
        @endcan
    </div>

    @if (session('mensaje'))
        <div class="alert success">{{ session('mensaje') }}</div>
    @endif

    <div class="card table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Título</th>
                    <th>Estado</th>
                    <th>Responsable</th>
                    <th><span class="sr-only">Acciones</span></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($incidentes as $incidente)
                    <tr wire:key="incidente-{{ $incidente->id }}">
                        <td class="code">{{ $incidente->codigo }}</td>
                        <td>{{ $incidente->titulo }}</td>
                        <td><span class="badge">{{ str($incidente->estado)->replace('_', ' ')->title() }}</span></td>
                        <td>{{ $incidente->responsable->nombre }}</td>
                        <td class="actions">
                            @can('update', $incidente)
                                <a href="{{ route('incidentes.edit', $incidente->id) }}" wire:navigate>Editar</a>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="empty">No hay incidentes registrados.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="pagination">{{ $incidentes->links() }}</div>
</section>
