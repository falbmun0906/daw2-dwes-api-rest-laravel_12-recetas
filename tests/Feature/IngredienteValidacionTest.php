<?php

namespace Tests\Feature;

use App\Models\Ingrediente;
use App\Models\Receta;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IngredienteValidacionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed', ['--class' => 'RoleSeeder']);
    }

    public function test_no_puede_crear_ingrediente_sin_nombre(): void
    {
        $user = User::factory()->create();
        $receta = Receta::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->postJson("/api/recetas/{$receta->id}/ingredientes", [
            'cantidad' => '100',
            'unidad' => 'g',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('nombre');
    }

    public function test_no_puede_crear_ingrediente_sin_cantidad(): void
    {
        $user = User::factory()->create();
        $receta = Receta::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->postJson("/api/recetas/{$receta->id}/ingredientes", [
            'nombre' => 'Huevo',
            'unidad' => 'ud',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('cantidad');
    }

    public function test_no_puede_crear_ingrediente_sin_unidad(): void
    {
        $user = User::factory()->create();
        $receta = Receta::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->postJson("/api/recetas/{$receta->id}/ingredientes", [
            'nombre' => 'Huevo',
            'cantidad' => '3',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('unidad');
    }

    public function test_nombre_ingrediente_no_puede_exceder_200_caracteres(): void
    {
        $user = User::factory()->create();
        $receta = Receta::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->postJson("/api/recetas/{$receta->id}/ingredientes", [
            'nombre' => str_repeat('a', 201),
            'cantidad' => '100',
            'unidad' => 'g',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('nombre');
    }

    public function test_cantidad_ingrediente_no_puede_exceder_50_caracteres(): void
    {
        $user = User::factory()->create();
        $receta = Receta::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->postJson("/api/recetas/{$receta->id}/ingredientes", [
            'nombre' => 'Azucar',
            'cantidad' => str_repeat('1', 51),
            'unidad' => 'g',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('cantidad');
    }

    public function test_unidad_ingrediente_no_puede_exceder_50_caracteres(): void
    {
        $user = User::factory()->create();
        $receta = Receta::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->postJson("/api/recetas/{$receta->id}/ingredientes", [
            'nombre' => 'Azucar',
            'cantidad' => '100',
            'unidad' => str_repeat('g', 51),
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('unidad');
    }

    public function test_no_puede_ver_ingrediente_de_receta_incorrecta(): void
    {
        $user = User::factory()->create();
        $receta1 = Receta::factory()->create(['user_id' => $user->id]);
        $receta2 = Receta::factory()->create(['user_id' => $user->id]);

        $ingrediente = Ingrediente::factory()->create(['receta_id' => $receta1->id]);

        $response = $this->actingAs($user)->getJson("/api/recetas/{$receta2->id}/ingredientes/{$ingrediente->id}");

        $response->assertStatus(404);
    }

    public function test_no_puede_actualizar_ingrediente_de_receta_incorrecta(): void
    {
        $user = User::factory()->create();
        $receta1 = Receta::factory()->create(['user_id' => $user->id]);
        $receta2 = Receta::factory()->create(['user_id' => $user->id]);

        $ingrediente = Ingrediente::factory()->create(['receta_id' => $receta1->id]);

        $response = $this->actingAs($user)->putJson(
            "/api/recetas/{$receta2->id}/ingredientes/{$ingrediente->id}",
            ['nombre' => 'Nuevo nombre']
        );

        $response->assertStatus(404);
    }

    public function test_no_puede_eliminar_ingrediente_de_receta_incorrecta(): void
    {
        $user = User::factory()->create();
        $receta1 = Receta::factory()->create(['user_id' => $user->id]);
        $receta2 = Receta::factory()->create(['user_id' => $user->id]);

        $ingrediente = Ingrediente::factory()->create(['receta_id' => $receta1->id]);

        $response = $this->actingAs($user)->deleteJson(
            "/api/recetas/{$receta2->id}/ingredientes/{$ingrediente->id}"
        );

        $response->assertStatus(404);
    }

    public function test_admin_puede_modificar_ingrediente_de_cualquier_receta(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $otroUsuario = User::factory()->create();
        $receta = Receta::factory()->create(['user_id' => $otroUsuario->id]);
        $ingrediente = Ingrediente::factory()->create(['receta_id' => $receta->id]);

        $response = $this->actingAs($admin)->putJson(
            "/api/recetas/{$receta->id}/ingredientes/{$ingrediente->id}",
            ['nombre' => 'Ingrediente Modificado por Admin']
        );

        $response->assertStatus(200);
        $this->assertDatabaseHas('ingredientes', [
            'id' => $ingrediente->id,
            'nombre' => 'Ingrediente Modificado por Admin',
        ]);
    }

    public function test_admin_puede_eliminar_ingrediente_de_cualquier_receta(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $otroUsuario = User::factory()->create();
        $receta = Receta::factory()->create(['user_id' => $otroUsuario->id]);
        $ingrediente = Ingrediente::factory()->create(['receta_id' => $receta->id]);

        $response = $this->actingAs($admin)->deleteJson(
            "/api/recetas/{$receta->id}/ingredientes/{$ingrediente->id}"
        );

        $response->assertStatus(200);
        $this->assertDatabaseMissing('ingredientes', ['id' => $ingrediente->id]);
    }
}
