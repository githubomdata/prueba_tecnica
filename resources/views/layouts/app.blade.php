<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $titulo ?? 'Mesa de Incidentes' }} · Mesa de Incidentes</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <header class="app-header">
        <a class="brand" href="{{ route('incidentes.index') }}" wire:navigate>Mesa de Incidentes</a>
        @auth
            <div class="user-menu">
                <span>{{ auth()->user()->name }}</span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="link-button" type="submit">Cerrar sesión</button>
                </form>
            </div>
        @endauth
    </header>

    <main class="container">
        {{ $slot ?? '' }}
        @yield('content')
    </main>
</body>
</html>
