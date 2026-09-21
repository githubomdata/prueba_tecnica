<?php

namespace Tests\Feature;

use App\Enums\PrioridadIncidente;
use Tests\TestCase;

class PrioridadIncidenteTest extends TestCase
{
    public function test_el_enum_base_expone_los_cuatro_valores_preparados(): void
    {
        $this->assertSame(
            ['baja', 'media', 'alta', 'critica'],
            array_column(PrioridadIncidente::cases(), 'value'),
        );
    }

    // Agregue aquí las pruebas de comportamiento solicitadas en el ejercicio.
}
