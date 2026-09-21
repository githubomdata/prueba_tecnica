<?php

namespace Tests\Feature;

use App\Actions\GuardarIncidente;
use App\Enums\PrioridadIncidente;
use App\Livewire\Incidentes\FormularioIncidente;
use App\Livewire\Incidentes\ListadoIncidentes;
use App\Models\Incidente;
use App\Models\Persona;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class IncidentesBaseTest extends TestCase
{
    use RefreshDatabase;

    public function test_un_invitado_no_puede_acceder_al_listado(): void
    {
        $this->get(route('incidentes.index'))->assertRedirect(route('login'));
    }

    public function test_un_usuario_autenticado_puede_leer_incidentes(): void
    {
        $usuario = User::factory()->lector()->create();
        $incidente = Incidente::factory()->create(['titulo' => 'Falla visible']);

        $this->actingAs($usuario)
            ->get(route('incidentes.index'))
            ->assertOk()
            ->assertSee('Falla visible')
            ->assertSee($incidente->responsable->nombre);
    }

    public function test_el_login_local_permite_ingresar(): void
    {
        $usuario = User::factory()->create([
            'email' => 'persona@mesa.test',
            'password' => 'clave-segura',
        ]);

        $this->post(route('login.store'), [
            'email' => $usuario->email,
            'password' => 'clave-segura',
        ])->assertRedirect(route('incidentes.index'));

        $this->assertAuthenticatedAs($usuario);
    }

    public function test_un_editor_puede_crear_un_incidente_con_el_flujo_inicial(): void
    {
        $editor = User::factory()->editor()->create();
        $responsable = Persona::factory()->create();

        Livewire::actingAs($editor)
            ->test(FormularioIncidente::class)
            ->set('titulo', 'Servicio sin respuesta')
            ->set('descripcion', 'El servicio ficticio no responde desde la red interna.')
            ->set('estado', 'abierto')
            ->set('responsable_id', $responsable->id)
            ->call('guardar')
            ->assertHasNoErrors()
            ->assertRedirect(route('incidentes.index'));

        $incidente = Incidente::query()->sole();

        $this->assertSame('Servicio sin respuesta', $incidente->titulo);
        $this->assertSame(PrioridadIncidente::MEDIA, $incidente->prioridad);
        $this->assertStringStartsWith('INC-', $incidente->codigo);
    }

    public function test_un_editor_puede_modificar_los_campos_existentes(): void
    {
        $editor = User::factory()->editor()->create();
        $responsable = Persona::factory()->create();
        $incidente = Incidente::factory()->create(['titulo' => 'Título anterior']);

        Livewire::actingAs($editor)
            ->test(FormularioIncidente::class, ['incidenteId' => $incidente->id])
            ->assertSet('titulo', 'Título anterior')
            ->set('titulo', 'Título actualizado')
            ->set('responsable_id', $responsable->id)
            ->call('guardar')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('incidentes', [
            'id' => $incidente->id,
            'titulo' => 'Título actualizado',
            'responsable_id' => $responsable->id,
        ]);
    }

    public function test_valida_campos_y_exige_un_responsable_activo(): void
    {
        $editor = User::factory()->editor()->create();
        $inactiva = Persona::factory()->inactiva()->create();

        Livewire::actingAs($editor)
            ->test(FormularioIncidente::class)
            ->set('titulo', '')
            ->set('descripcion', 'corta')
            ->set('responsable_id', $inactiva->id)
            ->call('guardar')
            ->assertHasErrors(['titulo', 'descripcion', 'responsable_id']);

        $this->assertDatabaseCount('incidentes', 0);
    }

    public function test_un_lector_no_puede_guardar_incidentes(): void
    {
        $lector = User::factory()->lector()->create();
        $responsable = Persona::factory()->create();

        $this->expectException(AuthorizationException::class);

        app(GuardarIncidente::class)->ejecutar($lector, [
            'titulo' => 'Intento no autorizado',
            'descripcion' => 'Este registro no debe quedar persistido.',
            'estado' => 'abierto',
            'responsable_id' => $responsable->id,
        ]);
    }

    public function test_el_listado_pagina_cinco_registros_en_orden_determinista(): void
    {
        $usuario = User::factory()->lector()->create();
        $responsable = Persona::factory()->create();

        foreach (range(1, 6) as $numero) {
            Incidente::factory()->create([
                'codigo' => "INC-PAG-{$numero}",
                'responsable_id' => $responsable->id,
            ]);
        }

        Livewire::actingAs($usuario)
            ->test(ListadoIncidentes::class)
            ->assertSee('INC-PAG-6')
            ->assertSee('INC-PAG-2')
            ->assertDontSee('INC-PAG-1');
    }
}
