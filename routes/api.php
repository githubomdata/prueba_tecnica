<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\IncidenteController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->name('api.v1.')->group(function () {
    Route::post('login', [AuthController::class, 'login'])->middleware('throttle:6,1')->name('login');

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout', [AuthController::class, 'logout'])->name('logout');
        Route::get('incidentes', [IncidenteController::class, 'index'])->name('incidentes.index');
        Route::post('incidentes', [IncidenteController::class, 'store'])->name('incidentes.store');
        Route::get('incidentes/{incidente}', [IncidenteController::class, 'show'])
            ->whereNumber('incidente')->name('incidentes.show');
        Route::patch('incidentes/{incidente}', [IncidenteController::class, 'update'])
            ->whereNumber('incidente')->name('incidentes.update');
    });
});
