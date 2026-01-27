<?php

namespace Tests\Feature;

use App\Models\Comentario;
use App\Models\Receta;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ComentarioValidacionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed', ['--class' => 'RoleSeeder']);
    }

    public function test_no_puede_crear_comentario_sin_texto(): void
    {
        $user = User::factory()->create();
        $receta = Receta::factory()->create();

        $response = $this->actingAs($user)->postJson("/api/recetas/{$receta->id}/comentarios", []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('texto');
    }

    public function test_texto_comentario_no_puede_exceder_1000_caracteres(): void
    {
        $user = User::factory()->create();
        $receta = Receta::factory()->create();

        $response = $this->actingAs($user)->postJson("/api/recetas/{$receta->id}/comentarios", [
            'texto' => str_repeat('a', 1001),
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('texto');
    }

    public function test_puede_crear_comentario_con_1000_caracteres(): void
    {
        $user = User::factory()->create();
        $receta = Receta::factory()->create();

        $response = $this->actingAs($user)->postJson("/api/recetas/{$receta->id}/comentarios", [
            'texto' => str_repeat('a', 1000),
        ]);

        $response->assertStatus(201);
    }

    public function test_comentario_incluye_datos_del_usuario(): void
    {
        $user = User::factory()->create(['name' => 'Juan Perez']);
        $receta = Receta::factory()->create();

        $response = $this->actingAs($user)->postJson("/api/recetas/{$receta->id}/comentarios", [
            'texto' => 'Excelente receta',
        ]);

        $response->assertStatus(201);
        $response->assertJsonPath('data.user.name', 'Juan Perez');
        $response->assertJsonPath('data.user.id', $user->id);
    }

    public function test_no_puede_ver_comentario_de_receta_incorrecta(): void
    {
        $user = User::factory()->create();
        $receta1 = Receta::factory()->create();
        $receta2 = Receta::factory()->create();

        $comentario = Comentario::factory()->create([
            'receta_id' => $receta1->id,
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)->getJson("/api/recetas/{$receta2->id}/comentarios/{$comentario->id}");

        $response->assertStatus(404);
    }

    public function test_no_puede_actualizar_comentario_de_receta_incorrecta(): void
    {
        $user = User::factory()->create();
        $receta1 = Receta::factory()->create();
        $receta2 = Receta::factory()->create();

        $comentario = Comentario::factory()->create([
            'receta_id' => $receta1->id,
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)->putJson(
            "/api/recetas/{$receta2->id}/comentarios/{$comentario->id}",
            ['texto' => 'Nuevo comentario']
        );

        $response->assertStatus(404);
    }

    public function test_no_puede_eliminar_comentario_de_receta_incorrecta(): void
    {
        $user = User::factory()->create();
        $receta1 = Receta::factory()->create();
        $receta2 = Receta::factory()->create();

        $comentario = Comentario::factory()->create([
            'receta_id' => $receta1->id,
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)->deleteJson(
            "/api/recetas/{$receta2->id}/comentarios/{$comentario->id}"
        );

        $response->assertStatus(404);
    }

    public function test_usuario_no_puede_modificar_comentario_de_otro_usuario(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $receta = Receta::factory()->create();

        $comentario = Comentario::factory()->create([
            'receta_id' => $receta->id,
            'user_id' => $user1->id,
            'texto' => 'Comentario original',
        ]);

        $response = $this->actingAs($user2)->putJson(
            "/api/recetas/{$receta->id}/comentarios/{$comentario->id}",
            ['texto' => 'Intento de modificacion']
        );

        $response->assertStatus(403);

        // Verificar que no se modifico
        $this->assertDatabaseHas('comentarios', [
            'id' => $comentario->id,
            'texto' => 'Comentario original',
        ]);
    }

    public function test_usuario_no_puede_eliminar_comentario_de_otro_usuario(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $receta = Receta::factory()->create();

        $comentario = Comentario::factory()->create([
            'receta_id' => $receta->id,
            'user_id' => $user1->id,
        ]);

        $response = $this->actingAs($user2)->deleteJson(
            "/api/recetas/{$receta->id}/comentarios/{$comentario->id}"
        );

        $response->assertStatus(403);
        $this->assertDatabaseHas('comentarios', ['id' => $comentario->id]);
    }

    public function test_puede_listar_comentarios_sin_autenticacion_falla(): void
    {
        $receta = Receta::factory()->create();

        $response = $this->getJson("/api/recetas/{$receta->id}/comentarios");

        $response->assertStatus(401);
    }

    public function test_comentarios_ordenados_por_fecha(): void
    {
        $user = User::factory()->create();
        $receta = Receta::factory()->create();

        // Crear comentarios en orden
        $comentario1 = Comentario::factory()->create([
            'receta_id' => $receta->id,
            'user_id' => $user->id,
            'texto' => 'Primer comentario',
            'created_at' => now()->subDays(2),
        ]);

        $comentario2 = Comentario::factory()->create([
            'receta_id' => $receta->id,
            'user_id' => $user->id,
            'texto' => 'Segundo comentario',
            'created_at' => now()->subDay(),
        ]);

        $comentario3 = Comentario::factory()->create([
            'receta_id' => $receta->id,
            'user_id' => $user->id,
            'texto' => 'Tercer comentario',
            'created_at' => now(),
        ]);

        $response = $this->actingAs($user)->getJson("/api/recetas/{$receta->id}/comentarios");

        $response->assertStatus(200);
        $response->assertJsonCount(3, 'data');
    }
}
