<?php

namespace Tests\Feature;

use App\Models\Receta;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LikeValidacionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed', ['--class' => 'RoleSeeder']);
    }

    public function test_no_puede_dar_like_sin_autenticacion(): void
    {
        $receta = Receta::factory()->create();

        $response = $this->postJson("/api/recetas/{$receta->id}/like");

        $response->assertStatus(401);
    }

    public function test_no_puede_consultar_likes_sin_autenticacion(): void
    {
        $receta = Receta::factory()->create();

        $response = $this->getJson("/api/recetas/{$receta->id}/likes");

        $response->assertStatus(401);
    }

    public function test_no_puede_consultar_estado_like_sin_autenticacion(): void
    {
        $receta = Receta::factory()->create();

        $response = $this->getJson("/api/recetas/{$receta->id}/like/status");

        $response->assertStatus(401);
    }

    public function test_like_aumenta_contador_correctamente(): void
    {
        $user = User::factory()->create();
        $receta = Receta::factory()->create();

        // Sin likes
        $response1 = $this->actingAs($user)->getJson("/api/recetas/{$receta->id}/likes");
        $response1->assertJsonPath('likes_count', 0);

        // Dar like
        $this->actingAs($user)->postJson("/api/recetas/{$receta->id}/like");

        // Con 1 like
        $response2 = $this->actingAs($user)->getJson("/api/recetas/{$receta->id}/likes");
        $response2->assertJsonPath('likes_count', 1);
    }

    public function test_quitar_like_disminuye_contador_correctamente(): void
    {
        $user = User::factory()->create();
        $receta = Receta::factory()->create();

        // Dar like
        $this->actingAs($user)->postJson("/api/recetas/{$receta->id}/like");

        // Verificar que tiene 1
        $response1 = $this->actingAs($user)->getJson("/api/recetas/{$receta->id}/likes");
        $response1->assertJsonPath('likes_count', 1);

        // Quitar like
        $this->actingAs($user)->postJson("/api/recetas/{$receta->id}/like");

        // Verificar que tiene 0
        $response2 = $this->actingAs($user)->getJson("/api/recetas/{$receta->id}/likes");
        $response2->assertJsonPath('likes_count', 0);
    }

    public function test_multiples_usuarios_pueden_dar_like_a_misma_receta(): void
    {
        $users = User::factory()->count(5)->create();
        $receta = Receta::factory()->create();

        foreach ($users as $user) {
            $this->actingAs($user)->postJson("/api/recetas/{$receta->id}/like");
        }

        $response = $this->actingAs($users[0])->getJson("/api/recetas/{$receta->id}/likes");
        $response->assertJsonPath('likes_count', 5);
    }

    public function test_estado_like_false_cuando_no_ha_dado_like(): void
    {
        $user = User::factory()->create();
        $receta = Receta::factory()->create();

        $response = $this->actingAs($user)->getJson("/api/recetas/{$receta->id}/like/status");

        $response->assertStatus(200);
        $response->assertJsonPath('liked', false);
    }

    public function test_estado_like_true_cuando_ha_dado_like(): void
    {
        $user = User::factory()->create();
        $receta = Receta::factory()->create();

        $this->actingAs($user)->postJson("/api/recetas/{$receta->id}/like");

        $response = $this->actingAs($user)->getJson("/api/recetas/{$receta->id}/like/status");

        $response->assertStatus(200);
        $response->assertJsonPath('liked', true);
    }

    public function test_like_se_elimina_cuando_se_elimina_usuario(): void
    {
        $user = User::factory()->create();
        $receta = Receta::factory()->create();

        $this->actingAs($user)->postJson("/api/recetas/{$receta->id}/like");

        // Verificar que existe el like
        $this->assertDatabaseHas('likes', [
            'user_id' => $user->id,
            'receta_id' => $receta->id,
        ]);

        // Eliminar usuario
        $user->delete();

        // Verificar que se elimino el like (por cascada)
        $this->assertDatabaseMissing('likes', [
            'receta_id' => $receta->id,
        ]);
    }

    public function test_like_se_elimina_cuando_se_elimina_receta(): void
    {
        $user = User::factory()->create();
        $receta = Receta::factory()->create();

        $this->actingAs($user)->postJson("/api/recetas/{$receta->id}/like");

        // Verificar que existe el like
        $this->assertDatabaseHas('likes', [
            'user_id' => $user->id,
            'receta_id' => $receta->id,
        ]);

        // Eliminar receta (necesita autorizacion)
        $this->actingAs(User::factory()->create()->assignRole('admin'))
            ->deleteJson("/api/recetas/{$receta->id}");

        // Verificar que se elimino el like (por cascada)
        $this->assertDatabaseMissing('likes', [
            'user_id' => $user->id,
        ]);
    }

    public function test_toggle_like_retorna_informacion_correcta_al_agregar(): void
    {
        $user = User::factory()->create();
        $receta = Receta::factory()->create();

        $response = $this->actingAs($user)->postJson("/api/recetas/{$receta->id}/like");

        $response->assertStatus(200);
        $response->assertJson([
            'message' => 'Like agregado',
            'liked' => true,
            'likes_count' => 1,
        ]);
    }

    public function test_toggle_like_retorna_informacion_correcta_al_quitar(): void
    {
        $user = User::factory()->create();
        $receta = Receta::factory()->create();

        // Primero dar like
        $this->actingAs($user)->postJson("/api/recetas/{$receta->id}/like");

        // Luego quitar like
        $response = $this->actingAs($user)->postJson("/api/recetas/{$receta->id}/like");

        $response->assertStatus(200);
        $response->assertJson([
            'message' => 'Like eliminado',
            'liked' => false,
            'likes_count' => 0,
        ]);
    }
}
