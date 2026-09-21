<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('incidentes', function (Blueprint $table) {
            $table->id();
            $table->string('codigo')->unique();
            $table->string('titulo');
            $table->text('descripcion');
            $table->string('estado')->default('abierto');
            $table->string('prioridad')->default('media');
            $table->foreignId('responsable_id')->constrained('personas');
            $table->timestamps();

            $table->index(['estado', 'id']);
            $table->index(['prioridad', 'id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('incidentes');
    }
};
