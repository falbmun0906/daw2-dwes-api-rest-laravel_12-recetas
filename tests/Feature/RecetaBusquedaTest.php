<?php

namespace Tests\Feature;

use App\Models\Ingrediente;
use App\Models\Like;
use App\Models\Receta;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecetaBusquedaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed', ['--class' => 'RoleSeeder']);
    }

    public function test_puede_buscar_recetas_por_titulo(): void
    {
        $user = User::factory()->create();

        Receta::factory()->create(['titulo' => 'Tortilla Española', 'user_id' => $user->id]);
        Receta::factory()->create(['titulo' => 'Paella Valenciana', 'user_id' => $user->id]);
        Receta::factory()->create(['titulo' => 'Gazpacho Andaluz', 'user_id' => $user->id]);

        $response = $this->actingAs($user)->getJson('/api/recetas?q=tortilla');

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.titulo', 'Tortilla Española');
    }

    public function test_puede_buscar_recetas_por_descripcion(): void
    {
        $user = User::factory()->create();

        Receta::factory()->create([
            'titulo' => 'Receta 1',
            'descripcion' => 'Una deliciosa sopa fría',
            'user_id' => $user->id
        ]);
        Receta::factory()->create([
            'titulo' => 'Receta 2',
            'descripcion' => 'Plato caliente tradicional',
            'user_id' => $user->id
        ]);

        $response = $this->actingAs($user)->getJson('/api/recetas?q=sopa');

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
    }

    public function test_puede_filtrar_recetas_por_ingrediente(): void
    {
        $user = User::factory()->create();

        $receta1 = Receta::factory()->create(['titulo' => 'Tortilla', 'user_id' => $user->id]);
        $receta2 = Receta::factory()->create(['titulo' => 'Paella', 'user_id' => $user->id]);

        Ingrediente::factory()->create(['receta_id' => $receta1->id, 'nombre' => 'Huevo']);
        Ingrediente::factory()->create(['receta_id' => $receta2->id, 'nombre' => 'Arroz']);

        $response = $this->actingAs($user)->getJson('/api/recetas?ingrediente=huevo');

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.titulo', 'Tortilla');
    }

    public function test_puede_filtrar_recetas_por_minimo_de_likes(): void
    {
        $user = User::factory()->create();
        $users = User::factory()->count(10)->create();

        $recetaPopular = Receta::factory()->create(['titulo' => 'Popular', 'user_id' => $user->id]);
        $recetaNormal = Receta::factory()->create(['titulo' => 'Normal', 'user_id' => $user->id]);

        // Dar 6 likes a la receta popular
        foreach ($users->take(6) as $u) {
            $recetaPopular->usuariosQueLesGusto()->attach($u->id);
        }

        // Dar 2 likes a la receta normal
        foreach ($users->take(2) as $u) {
            $recetaNormal->usuariosQueLesGusto()->attach($u->id);
        }

        $response = $this->actingAs($user)->getJson('/api/recetas?min_likes=5');

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.titulo', 'Popular');
    }

    public function test_puede_ordenar_recetas_por_titulo_ascendente(): void
    {
        $user = User::factory()->create();

        Receta::factory()->create(['titulo' => 'Zebra Cake', 'user_id' => $user->id]);
        Receta::factory()->create(['titulo' => 'Apple Pie', 'user_id' => $user->id]);
        Receta::factory()->create(['titulo' => 'Mango Juice', 'user_id' => $user->id]);

        $response = $this->actingAs($user)->getJson('/api/recetas?sort=titulo');

        $response->assertStatus(200);
        $response->assertJsonPath('data.0.titulo', 'Apple Pie');
        $response->assertJsonPath('data.1.titulo', 'Mango Juice');
        $response->assertJsonPath('data.2.titulo', 'Zebra Cake');
    }

    public function test_puede_ordenar_recetas_por_titulo_descendente(): void
    {
        $user = User::factory()->create();

        Receta::factory()->create(['titulo' => 'Apple Pie', 'user_id' => $user->id]);
        Receta::factory()->create(['titulo' => 'Zebra Cake', 'user_id' => $user->id]);

        $response = $this->actingAs($user)->getJson('/api/recetas?sort=-titulo');

        $response->assertStatus(200);
        $response->assertJsonPath('data.0.titulo', 'Zebra Cake');
        $response->assertJsonPath('data.1.titulo', 'Apple Pie');
    }

    public function test_puede_ordenar_recetas_por_popularidad(): void
    {
        $user = User::factory()->create();
        $users = User::factory()->count(5)->create();

        $receta1 = Receta::factory()->create(['titulo' => 'Menos Popular', 'user_id' => $user->id]);
        $receta2 = Receta::factory()->create(['titulo' => 'Mas Popular', 'user_id' => $user->id]);

        // 1 like a receta1
        $receta1->usuariosQueLesGusto()->attach($users->first()->id);

        // 3 likes a receta2
        foreach ($users->take(3) as $u) {
            $receta2->usuariosQueLesGusto()->attach($u->id);
        }

        $response = $this->actingAs($user)->getJson('/api/recetas?sort=-likes_count');

        $response->assertStatus(200);
        $response->assertJsonPath('data.0.titulo', 'Mas Popular');
        $response->assertJsonPath('data.1.titulo', 'Menos Popular');
    }

    public function test_puede_combinar_multiples_filtros(): void
    {
        $user = User::factory()->create();
        $users = User::factory()->count(5)->create();

        $receta1 = Receta::factory()->create([
            'titulo' => 'Tortilla con Huevo',
            'descripcion' => 'Deliciosa tortilla',
            'user_id' => $user->id
        ]);
        $receta2 = Receta::factory()->create([
            'titulo' => 'Otra Tortilla',
            'descripcion' => 'Sin mucho éxito',
            'user_id' => $user->id
        ]);

        Ingrediente::factory()->create(['receta_id' => $receta1->id, 'nombre' => 'Huevo']);
        Ingrediente::factory()->create(['receta_id' => $receta2->id, 'nombre' => 'Huevo']);

        // Solo receta1 tiene 3 likes
        foreach ($users->take(3) as $u) {
            $receta1->usuariosQueLesGusto()->attach($u->id);
        }

        // Solo 1 like a receta2
        $receta2->usuariosQueLesGusto()->attach($users->first()->id);

        $response = $this->actingAs($user)->getJson('/api/recetas?q=tortilla&ingrediente=huevo&min_likes=2');

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.titulo', 'Tortilla con Huevo');
    }

    public function test_paginacion_funciona_correctamente(): void
    {
        $user = User::factory()->create();

        Receta::factory()->count(25)->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->getJson('/api/recetas?per_page=10');

        $response->assertStatus(200);
        $response->assertJsonCount(10, 'data');
        $response->assertJsonStructure([
            'data',
            'links',
            'meta' => [
                'current_page',
                'total',
                'per_page'
            ]
        ]);
    }

    public function test_respeta_limite_maximo_de_items_por_pagina(): void
    {
        $user = User::factory()->create();

        Receta::factory()->count(100)->create(['user_id' => $user->id]);

        // Intentar solicitar 100 items, pero el máximo es 50
        $response = $this->actingAs($user)->getJson('/api/recetas?per_page=100');

        $response->assertStatus(200);
        $response->assertJsonCount(50, 'data');
    }
}
