@extends('layouts.app', ['titulo' => 'Iniciar sesión'])

@section('content')
    <section class="card login-card">
        <h1>Iniciar sesión</h1>
        <p class="muted">Acceso local para la evaluación.</p>

        <form method="POST" action="{{ route('login.store') }}" class="stack">
            @csrf
            <label>
                Correo
                <input type="email" name="email" value="{{ old('email') }}" required autofocus>
                @error('email') <span class="error">{{ $message }}</span> @enderror
            </label>
            <label>
                Contraseña
                <input type="password" name="password" required>
                @error('password') <span class="error">{{ $message }}</span> @enderror
            </label>
            <label class="checkbox"><input type="checkbox" name="remember"> Recordarme</label>
            <button class="button" type="submit">Ingresar</button>
        </form>
    </section>
@endsection
