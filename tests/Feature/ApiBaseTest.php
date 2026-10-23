<?php

namespace Tests\Feature;

use App\Enums\PrioridadIncidente;
use App\Models\Incidente;
use App\Models\Persona;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ApiBaseTest extends TestCase
{
    use RefreshDatabase;

    public static function perfiles(): array
    {
        return ['editor' => [true], 'lector' => [false]];
    }

    #[DataProvider('perfiles')]
    public function test_ambos_perfiles_obtienen_un_token_real_sin_exponer_datos_internos(bool $puedeEditar): void
    {
        $usuario = User::factory()->create([
            'email' => $puedeEditar ? 'editor@mesa.test' : 'lector@mesa.test',
            'password' => 'Evaluacion2026!',
            'puede_editar' => $puedeEditar,
        ]);

        $respuesta = $this->api('POST', '/api/v1/login', datos: [
            'email' => $usuario->email,
            'password' => 'Evaluacion2026!',
        ])->assertOk()
            ->assertJsonPath('token_type', 'Bearer')
            ->assertJsonPath('data.email', $usuario->email)
            ->assertJsonMissingPath('data.password')
            ->assertJsonMissingPath('data.puede_editar')
            ->assertJsonMissingPath('data.remember_token');

        $token = $respuesta->json('token');
        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_id' => $usuario->id,
            'token' => hash('sha256', explode('|', $token, 2)[1]),
        ]);
        $this->assertNotNull($respuesta->json('expires_at'));
        $incidente = Incidente::factory()->create();
        $this->api('GET', '/api/v1/incidentes/'.$incidente->id, $token)
            ->assertOk()->assertJsonPath('data.id', $incidente->id);
    }

    public function test_el_login_rechaza_credenciales_incorrectas_y_datos_incompletos(): void
    {
        $usuario = User::factory()->create(['password' => 'correcta']);

        $this->api('POST', '/api/v1/login', datos: [
            'email' => $usuario->email, 'password' => 'incorrecta',
        ])->assertUnauthorized()->assertJsonMissingPath('token');
        $this->api('POST', '/api/v1/login', datos: [
            'email' => 'inexistente@mesa.test', 'password' => 'incorrecta',
        ])->assertUnauthorized();
        $this->api('POST', '/api/v1/login')->assertUnprocessable()->assertJsonValidationErrors(['email', 'password']);
        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public static function operacionesProtegidas(): array
    {
        return [
            ['GET', '/api/v1/incidentes'],
            ['GET', '/api/v1/incidentes/1'],
            ['POST', '/api/v1/incidentes'],
            ['PATCH', '/api/v1/incidentes/1'],
            ['POST', '/api/v1/logout'],
        ];
    }

    public function test_el_login_limita_intentos_y_conserva_retry_after(): void
    {
        foreach (range(1, 6) as $intento) {
            $this->api('POST', '/api/v1/login', datos: [
                'email' => 'inexistente@mesa.test', 'password' => 'incorrecta',
            ])->assertUnauthorized();
        }

        $this->api('POST', '/api/v1/login', datos: [
            'email' => 'inexistente@mesa.test', 'password' => 'incorrecta',
        ])->assertTooManyRequests()->assertHeader('Retry-After')->assertJsonMissingPath('trace');
    }

    #[DataProvider('operacionesProtegidas')]
    public function test_los_endpoints_privados_exigen_token(string $metodo, string $ruta): void
    {
        $this->api($metodo, $ruta)->assertUnauthorized()->assertJsonStructure(['message']);
    }

    public function test_una_sesion_web_no_sustituye_el_token_de_api(): void
    {
        $this->actingAs(User::factory()->editor()->create(), 'web')
            ->getJson('/api/v1/incidentes')->assertUnauthorized();
    }

    public function test_los_tokens_invalidos_y_expirados_no_autentican(): void
    {
        $usuario = User::factory()->create();
        $expirado = $usuario->createToken('expirado', ['*'], now()->subMinute())->plainTextToken;

        $this->api('GET', '/api/v1/incidentes', 'token-invalido')->assertUnauthorized();
        $this->api('GET', '/api/v1/incidentes', $expirado)->assertUnauthorized();
    }

    #[DataProvider('perfiles')]
    public function test_el_listado_respeta_el_estado_de_la_plantilla_y_ambos_perfiles_consultan_detalle(bool $puedeEditar): void
    {
        $usuario = User::factory()->create(['puede_editar' => $puedeEditar]);
        $token = $usuario->createToken('consulta')->plainTextToken;
        $incidentes = Incidente::factory()->count(6)->create();

        $listado = $this->api('GET', '/api/v1/incidentes', $token);

        if ($this->esListadoPendiente($listado)) {
            $this->assertTrue($this->esListadoPendiente(
                $this->api('GET', '/api/v1/incidentes?page=2', $token),
            ));
        } else {
            $listado->assertOk()
                ->assertJsonCount(5, 'data')
                ->assertJsonPath('meta.per_page', 5)
                ->assertJsonPath('meta.total', 6)
                ->assertJsonPath('data.0.id', $incidentes->last()->id)
                ->assertJsonStructure(['data', 'links', 'meta'])
                ->assertJsonMissingPath('data.0.responsable.email');
            $this->api('GET', '/api/v1/incidentes?page=2', $token)->assertOk()
                ->assertJsonCount(1, 'data')->assertJsonPath('data.0.id', $incidentes->first()->id);
        }
        $this->api('GET', '/api/v1/incidentes/'.$incidentes->first()->id, $token)->assertOk()
            ->assertJsonPath('data.titulo', $incidentes->first()->titulo)
            ->assertJsonPath('data.responsable.id', $incidentes->first()->responsable_id)
            ->assertJsonPath('data.prioridad', 'media')
            ->assertJsonPath('data.prioridad_etiqueta', 'Media')
            ->assertJsonMissingPath('data.responsable.email');
    }

    public function test_un_listado_vacio_respeta_la_plantilla_o_conserva_el_contrato_si_esta_habilitado(): void
    {
        $token = User::factory()->lector()->create()->createToken('consulta')->plainTextToken;

        $listado = $this->api('GET', '/api/v1/incidentes', $token);

        if (! $this->esListadoPendiente($listado)) {
            $listado->assertOk()
                ->assertJsonPath('data', [])->assertJsonPath('meta.total', 0)->assertJsonPath('meta.per_page', 5);
        }
    }

    public function test_el_editor_crea_con_prioridad_media_y_los_datos_se_consultan_despues(): void
    {
        $token = User::factory()->editor()->create()->createToken('editor')->plainTextToken;
        $responsable = Persona::factory()->create();

        $respuesta = $this->api('POST', '/api/v1/incidentes', $token, [
            'titulo' => 'Incidente creado por API',
            'descripcion' => 'Descripción suficiente para la validación del incidente.',
            'estado' => 'abierto',
            'responsable_id' => $responsable->id,
            'codigo' => 'CODIGO-INYECTADO',
            'id' => 999999,
            'prioridad' => 'critica',
            'puede_editar' => true,
        ])->assertCreated()->assertJsonPath('data.prioridad', 'media')
            ->assertJsonPath('data.responsable.id', $responsable->id);

        $id = $respuesta->json('data.id');
        $this->assertNotSame(999999, $id);
        $this->assertStringStartsWith('INC-', $respuesta->json('data.codigo'));
        $respuesta->assertHeader('Location', route('api.v1.incidentes.show', $id));
        $this->assertDatabaseHas('incidentes', ['id' => $id, 'prioridad' => 'media', 'responsable_id' => $responsable->id]);
        $this->api('GET', '/api/v1/incidentes/'.$id, $token)
            ->assertOk()->assertJsonPath('data.titulo', 'Incidente creado por API');
    }

    public function test_patch_actualiza_solo_campos_permitidos_y_conserva_los_omitidos(): void
    {
        $token = User::factory()->editor()->create()->createToken('editor')->plainTextToken;
        $incidente = Incidente::factory()->create(['prioridad' => PrioridadIncidente::ALTA]);
        $antes = $incidente->fresh()->getAttributes();

        $this->api('PATCH', '/api/v1/incidentes/'.$incidente->id, $token, [
            'titulo' => 'Título actualizado por API',
            'codigo' => 'CODIGO-INYECTADO',
            'id' => 999999,
            'prioridad' => 'baja',
            'puede_editar' => true,
        ])->assertOk()->assertJsonPath('data.titulo', 'Título actualizado por API');

        $despues = $incidente->fresh()->getAttributes();
        foreach (['id', 'codigo', 'descripcion', 'estado', 'responsable_id', 'prioridad', 'created_at'] as $campo) {
            $this->assertSame($antes[$campo], $despues[$campo], $campo);
        }
        $this->api('GET', '/api/v1/incidentes/'.$incidente->id, $token)
            ->assertOk()->assertJsonPath('data.titulo', 'Título actualizado por API');
    }

    public function test_el_lector_no_puede_crear_ni_actualizar_aunque_envie_permisos(): void
    {
        $token = User::factory()->lector()->create()->createToken('lector')->plainTextToken;
        $incidente = Incidente::factory()->create();
        $antes = $incidente->fresh()->getAttributes();

        $this->api('POST', '/api/v1/incidentes', $token, ['puede_editar' => true])->assertForbidden();
        $this->api('PATCH', '/api/v1/incidentes/'.$incidente->id, $token, [
            'titulo' => 'Intento prohibido', 'puede_editar' => true,
        ])->assertForbidden();
        $this->assertDatabaseCount('incidentes', 1);
        $this->assertSame($antes, $incidente->fresh()->getAttributes());
    }

    public function test_los_permisos_se_reconsultan_en_backend_para_un_token_existente(): void
    {
        $usuario = User::factory()->editor()->create();
        $token = $usuario->createToken('editor')->plainTextToken;
        $incidente = Incidente::factory()->create();
        $usuario->update(['puede_editar' => false]);

        $this->api('PATCH', '/api/v1/incidentes/'.$incidente->id, $token, ['titulo' => 'Prohibido'])
            ->assertForbidden();
    }

    public function test_la_api_valida_los_campos_reales_y_el_responsable_activo(): void
    {
        $token = User::factory()->editor()->create()->createToken('editor')->plainTextToken;
        $inactiva = Persona::factory()->inactiva()->create();

        $this->api('POST', '/api/v1/incidentes', $token, [
            'titulo' => '', 'descripcion' => 'corta', 'estado' => 'invalido', 'responsable_id' => $inactiva->id,
        ])->assertUnprocessable()->assertJsonValidationErrors(['titulo', 'descripcion', 'estado', 'responsable_id']);
        $this->api('POST', '/api/v1/incidentes', $token)->assertUnprocessable()
            ->assertJsonValidationErrors(['titulo', 'descripcion', 'estado', 'responsable_id']);
        $this->assertDatabaseCount('incidentes', 0);

        $incidente = Incidente::factory()->create();
        $antes = $incidente->fresh()->getAttributes();
        $this->api('PATCH', '/api/v1/incidentes/'.$incidente->id, $token, [
            'titulo' => null, 'estado' => 'invalido', 'responsable_id' => 999999,
        ])->assertUnprocessable()->assertJsonValidationErrors(['titulo', 'estado', 'responsable_id']);
        $this->assertSame($antes, $incidente->fresh()->getAttributes());
    }

    public function test_los_recursos_inexistentes_y_errores_se_devuelven_como_json_sin_trazas(): void
    {
        config(['app.debug' => true]);
        $token = User::factory()->editor()->create()->createToken('editor')->plainTextToken;

        $this->api('GET', '/api/v1/incidentes/999999', $token)->assertNotFound()
            ->assertExactJson(['message' => 'Recurso no encontrado.']);
        $this->api('PATCH', '/api/v1/incidentes/999999', $token, ['titulo' => 'No existe'])->assertNotFound();
        app('auth')->forgetGuards();
        $this->get('/api/v1/no-existe')->assertNotFound()->assertHeader('Content-Type', 'application/json');
        $this->get('/api/v1/incidentes')->assertUnauthorized()->assertHeader('Content-Type', 'application/json');

        Route::get('api/v1/error-interno-de-prueba', function () {
            throw new \RuntimeException('Detalle interno que no debe exponerse');
        });
        $this->api('GET', '/api/v1/error-interno-de-prueba')->assertInternalServerError()
            ->assertExactJson(['message' => 'No fue posible completar la solicitud.']);
    }

    public function test_logout_revoca_solo_el_token_actual(): void
    {
        $usuario = User::factory()->create();
        $actual = $usuario->createToken('actual')->plainTextToken;
        $otro = $usuario->createToken('otro')->plainTextToken;
        $incidente = Incidente::factory()->create();

        $this->api('POST', '/api/v1/logout', $actual)->assertOk();
        $this->assertDatabaseCount('personal_access_tokens', 1);
        $this->api('GET', '/api/v1/incidentes/'.$incidente->id, $actual)->assertUnauthorized();
        $this->api('GET', '/api/v1/incidentes/'.$incidente->id, $otro)->assertOk();
    }

    private function esListadoPendiente(TestResponse $respuesta): bool
    {
        // La suite base admite la plantilla inicial y comprueba la paginación si se habilita.
        // La aceptación de la Fase 2 exige un listado 200 y se evalúa por separado.
        if ($respuesta->status() !== 403) {
            return false;
        }

        $respuesta->assertForbidden()
            ->assertExactJson(['message' => 'No tienes permiso para realizar esta operación.']);

        return true;
    }

    private function api(string $metodo, string $ruta, ?string $token = null, array $datos = []): TestResponse
    {
        // Cada solicitud real tiene un guard nuevo; se reproduce ese límite en el cliente de pruebas.
        app('auth')->forgetGuards();

        return $this->json($metodo, $ruta, $datos, [
            'Authorization' => $token ? 'Bearer '.$token : '',
            'Accept' => 'application/json',
        ]);
    }
}
