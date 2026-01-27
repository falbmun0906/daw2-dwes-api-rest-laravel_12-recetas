<?php

namespace Tests\Feature;

use App\Models\Receta;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecetaValidacionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed', ['--class' => 'RoleSeeder']);
    }

    public function test_no_puede_crear_receta_sin_titulo(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/recetas', [
            'descripcion' => 'Descripcion',
            'instrucciones' => 'Instrucciones',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('titulo');
    }

    public function test_no_puede_crear_receta_sin_descripcion(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/recetas', [
            'titulo' => 'Titulo',
            'instrucciones' => 'Instrucciones',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('descripcion');
    }

    public function test_no_puede_crear_receta_sin_instrucciones(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/recetas', [
            'titulo' => 'Titulo',
            'descripcion' => 'Descripcion',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('instrucciones');
    }

    public function test_titulo_no_puede_exceder_200_caracteres(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/recetas', [
            'titulo' => str_repeat('a', 201),
            'descripcion' => 'Descripcion',
            'instrucciones' => 'Instrucciones',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('titulo');
    }

    public function test_puede_crear_receta_con_titulo_de_200_caracteres(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/recetas', [
            'titulo' => str_repeat('a', 200),
            'descripcion' => 'Descripcion',
            'instrucciones' => 'Instrucciones',
        ]);

        $response->assertStatus(201);
    }

    public function test_no_puede_actualizar_receta_con_datos_vacios(): void
    {
        $user = User::factory()->create();
        $receta = Receta::factory()->create([
            'user_id' => $user->id,
            'publicada' => false,
        ]);

        $response = $this->actingAs($user)->putJson("/api/recetas/{$receta->id}", [
            'titulo' => '',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('titulo');
    }

    public function test_receta_no_autenticado_retorna_401(): void
    {
        $response = $this->postJson('/api/recetas', [
            'titulo' => 'Titulo',
            'descripcion' => 'Descripcion',
            'instrucciones' => 'Instrucciones',
        ]);

        $response->assertStatus(401);
    }

    public function test_ver_receta_no_autenticado_retorna_401(): void
    {
        $receta = Receta::factory()->create();

        $response = $this->getJson("/api/recetas/{$receta->id}");

        $response->assertStatus(401);
    }

    public function test_actualizar_receta_no_autenticado_retorna_401(): void
    {
        $receta = Receta::factory()->create(['publicada' => false]);

        $response = $this->putJson("/api/recetas/{$receta->id}", [
            'titulo' => 'Nuevo titulo',
        ]);

        $response->assertStatus(401);
    }

    public function test_eliminar_receta_no_autenticado_retorna_401(): void
    {
        $receta = Receta::factory()->create();

        $response = $this->deleteJson("/api/recetas/{$receta->id}");

        $response->assertStatus(401);
    }
}
