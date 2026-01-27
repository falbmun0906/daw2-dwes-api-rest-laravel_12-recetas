<?php

namespace Tests\Feature;

use App\Models\Receta;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LikeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed', ['--class' => 'RoleSeeder']);
    }

    public function test_usuario_puede_dar_like_a_receta(): void
    {
        $user = User::factory()->create();
        $receta = Receta::factory()->create();

        $response = $this->actingAs($user)->postJson("/api/recetas/{$receta->id}/like");

        $response->assertStatus(200);
        $response->assertJson([
            'liked' => true,
            'likes_count' => 1,
        ]);

        $this->assertDatabaseHas('likes', [
            'user_id' => $user->id,
            'receta_id' => $receta->id,
        ]);
    }

    public function test_usuario_puede_quitar_like_a_receta(): void
    {
        $user = User::factory()->create();
        $receta = Receta::factory()->create();

        // Primero dar like
        $receta->likes()->attach($user->id);

        $response = $this->actingAs($user)->postJson("/api/recetas/{$receta->id}/like");

        $response->assertStatus(200);
        $response->assertJson([
            'liked' => false,
            'likes_count' => 0,
        ]);

        $this->assertDatabaseMissing('likes', [
            'user_id' => $user->id,
            'receta_id' => $receta->id,
        ]);
    }

    public function test_usuario_no_puede_dar_mas_de_un_like_a_la_misma_receta(): void
    {
        $user = User::factory()->create();
        $receta = Receta::factory()->create();

        // Primer like
        $this->actingAs($user)->postJson("/api/recetas/{$receta->id}/like");

        // Segundo like (debería quitar el like)
        $response = $this->actingAs($user)->postJson("/api/recetas/{$receta->id}/like");

        $response->assertStatus(200);
        $response->assertJson([
            'liked' => false,
        ]);
    }

    public function test_puede_consultar_numero_de_likes_de_receta(): void
    {
        $users = User::factory()->count(5)->create();
        $receta = Receta::factory()->create();

        // Dar likes
        foreach ($users as $user) {
            $receta->likes()->attach($user->id);
        }

        $response = $this->actingAs($users[0])->getJson("/api/recetas/{$receta->id}/likes");

        $response->assertStatus(200);
        $response->assertJson([
            'likes_count' => 5,
        ]);
    }

    public function test_puede_consultar_estado_de_like_de_usuario(): void
    {
        $user = User::factory()->create();
        $receta = Receta::factory()->create();

        $receta->likes()->attach($user->id);

        $response = $this->actingAs($user)->getJson("/api/recetas/{$receta->id}/like/status");

        $response->assertStatus(200);
        $response->assertJson([
            'liked' => true,
        ]);
    }
}
