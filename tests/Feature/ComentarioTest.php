<?php

namespace Tests\Feature;

use App\Models\Comentario;
use App\Models\Receta;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ComentarioTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed', ['--class' => 'RoleSeeder']);
    }

    public function test_usuario_puede_comentar_receta(): void
    {
        $user = User::factory()->create();
        $receta = Receta::factory()->create();

        $response = $this->actingAs($user)->postJson("/api/recetas/{$receta->id}/comentarios", [
            'texto' => 'Muy buena receta',
        ]);

        $response->assertStatus(201);
        $response->assertJsonStructure([
            'data' => [
                'id',
                'texto',
                'user_id',
                'receta_id',
                'user' => ['id', 'name', 'email'],
            ],
        ]);

        $this->assertDatabaseHas('comentarios', [
            'receta_id' => $receta->id,
            'user_id' => $user->id,
            'texto' => 'Muy buena receta',
        ]);
    }

    public function test_usuario_puede_listar_comentarios_de_receta(): void
    {
        $user = User::factory()->create();
        $receta = Receta::factory()->create();

        Comentario::factory()->count(3)->create([
            'receta_id' => $receta->id,
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)->getJson("/api/recetas/{$receta->id}/comentarios");

        $response->assertStatus(200);
        $response->assertJsonCount(3, 'data');
    }

    public function test_usuario_puede_editar_su_propio_comentario(): void
    {
        $user = User::factory()->create();
        $receta = Receta::factory()->create();
        $comentario = Comentario::factory()->create([
            'receta_id' => $receta->id,
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)->putJson(
            "/api/recetas/{$receta->id}/comentarios/{$comentario->id}",
            ['texto' => 'Comentario actualizado']
        );

        $response->assertStatus(200);
        $this->assertDatabaseHas('comentarios', [
            'id' => $comentario->id,
            'texto' => 'Comentario actualizado',
        ]);
    }

    public function test_usuario_no_puede_editar_comentario_ajeno(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $receta = Receta::factory()->create();

        $comentario = Comentario::factory()->create([
            'receta_id' => $receta->id,
            'user_id' => $user1->id,
        ]);

        $response = $this->actingAs($user2)->putJson(
            "/api/recetas/{$receta->id}/comentarios/{$comentario->id}",
            ['texto' => 'Intento de modificación']
        );

        $response->assertStatus(403);
    }

    public function test_usuario_puede_eliminar_su_propio_comentario(): void
    {
        $user = User::factory()->create();
        $receta = Receta::factory()->create();
        $comentario = Comentario::factory()->create([
            'receta_id' => $receta->id,
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)->deleteJson(
            "/api/recetas/{$receta->id}/comentarios/{$comentario->id}"
        );

        $response->assertStatus(200);
        $this->assertDatabaseMissing('comentarios', ['id' => $comentario->id]);
    }

    public function test_admin_puede_eliminar_cualquier_comentario(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $user = User::factory()->create();
        $receta = Receta::factory()->create();
        $comentario = Comentario::factory()->create([
            'receta_id' => $receta->id,
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($admin)->deleteJson(
            "/api/recetas/{$receta->id}/comentarios/{$comentario->id}"
        );

        $response->assertStatus(200);
        $this->assertDatabaseMissing('comentarios', ['id' => $comentario->id]);
    }
}
