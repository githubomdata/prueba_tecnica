<?php

use App\Http\Controllers\AuthController;
use App\Livewire\Incidentes\FormularioIncidente;
use App\Livewire\Incidentes\ListadoIncidentes;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route(auth()->check() ? 'incidentes.index' : 'login'));

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'create'])->name('login');
    Route::post('/login', [AuthController::class, 'store'])->name('login.store');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'destroy'])->name('logout');
    Route::get('/incidentes', ListadoIncidentes::class)->name('incidentes.index');
    Route::get('/incidentes/nuevo', FormularioIncidente::class)->name('incidentes.create');
    Route::get('/incidentes/{incidenteId}/editar', FormularioIncidente::class)->name('incidentes.edit');
});
