<?php

namespace Tests\Feature;

use App\Models\Ingrediente;
use App\Models\Receta;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IngredienteTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed', ['--class' => 'RoleSeeder']);
    }

    public function test_usuario_puede_agregar_ingrediente_a_su_receta(): void
    {
        $user = User::factory()->create();
        $receta = Receta::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->postJson("/api/recetas/{$receta->id}/ingredientes", [
            'nombre' => 'Huevo',
            'cantidad' => '3',
            'unidad' => 'ud',
        ]);

        $response->assertStatus(201);
        $response->assertJsonStructure([
            'data' => [
                'id',
                'nombre',
                'cantidad',
                'unidad',
                'receta_id',
            ],
        ]);

        $this->assertDatabaseHas('ingredientes', [
            'receta_id' => $receta->id,
            'nombre' => 'Huevo',
            'cantidad' => '3',
            'unidad' => 'ud',
        ]);
    }

    public function test_usuario_puede_listar_ingredientes_de_una_receta(): void
    {
        $user = User::factory()->create();
        $receta = Receta::factory()->create(['user_id' => $user->id]);

        Ingrediente::factory()->count(3)->create(['receta_id' => $receta->id]);

        $response = $this->actingAs($user)->getJson("/api/recetas/{$receta->id}/ingredientes");

        $response->assertStatus(200);
        $response->assertJsonCount(3, 'data');
    }

    public function test_usuario_no_puede_modificar_ingrediente_de_otra_receta(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $receta = Receta::factory()->create(['user_id' => $user1->id]);
        $ingrediente = Ingrediente::factory()->create(['receta_id' => $receta->id]);

        $response = $this->actingAs($user2)->putJson(
            "/api/recetas/{$receta->id}/ingredientes/{$ingrediente->id}",
            ['nombre' => 'Ingrediente Modificado']
        );

        $response->assertStatus(403);
    }

    public function test_usuario_puede_eliminar_ingrediente_de_su_receta(): void
    {
        $user = User::factory()->create();
        $receta = Receta::factory()->create(['user_id' => $user->id]);
        $ingrediente = Ingrediente::factory()->create(['receta_id' => $receta->id]);

        $response = $this->actingAs($user)->deleteJson(
            "/api/recetas/{$receta->id}/ingredientes/{$ingrediente->id}"
        );

        $response->assertStatus(200);
        $this->assertDatabaseMissing('ingredientes', ['id' => $ingrediente->id]);
    }
}
