<?php

namespace Tests\Feature;

use App\Models\Comentario;
use App\Models\Ingrediente;
use App\Models\Receta;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecetaIntegracionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed', ['--class' => 'RoleSeeder']);
    }

    public function test_puede_crear_receta_completa_con_ingredientes_y_likes(): void
    {
        $user = User::factory()->create();

        // Crear receta
        $response = $this->actingAs($user)->postJson('/api/recetas', [
            'titulo' => 'Tortilla Completa',
            'descripcion' => 'Descripcion completa',
            'instrucciones' => 'Instrucciones detalladas',
        ]);

        $recetaId = $response->json('id');

        // Agregar ingredientes
        $this->actingAs($user)->postJson("/api/recetas/{$recetaId}/ingredientes", [
            'nombre' => 'Huevos',
            'cantidad' => '4',
            'unidad' => 'ud',
        ]);

        $this->actingAs($user)->postJson("/api/recetas/{$recetaId}/ingredientes", [
            'nombre' => 'Patatas',
            'cantidad' => '500',
            'unidad' => 'g',
        ]);

        // Dar like
        $this->actingAs($user)->postJson("/api/recetas/{$recetaId}/like");

        // Agregar comentario
        $this->actingAs($user)->postJson("/api/recetas/{$recetaId}/comentarios", [
            'texto' => 'Mi propia receta!',
        ]);

        // Verificar todo
        $response = $this->actingAs($user)->getJson("/api/recetas/{$recetaId}");

        $response->assertStatus(200);
        $response->assertJsonPath('data.titulo', 'Tortilla Completa');
        $response->assertJsonCount(2, 'data.ingredientes');
        $response->assertJsonPath('data.likes_count', 1);
        $response->assertJsonCount(1, 'data.comentarios');
    }

    public function test_eliminar_receta_elimina_ingredientes_comentarios_y_likes(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $user = User::factory()->create();
        $receta = Receta::factory()->create(['user_id' => $user->id]);

        // Crear relaciones
        $ingrediente = Ingrediente::factory()->create(['receta_id' => $receta->id]);
        $comentario = Comentario::factory()->create([
            'receta_id' => $receta->id,
            'user_id' => $user->id,
        ]);
        $receta->usuariosQueLesGusto()->attach($user->id);

        // Verificar que existen
        $this->assertDatabaseHas('ingredientes', ['id' => $ingrediente->id]);
        $this->assertDatabaseHas('comentarios', ['id' => $comentario->id]);
        $this->assertDatabaseHas('likes', ['receta_id' => $receta->id]);

        // Eliminar receta
        $this->actingAs($admin)->deleteJson("/api/recetas/{$receta->id}");

        // Verificar que se eliminaron en cascada
        $this->assertDatabaseMissing('recetas', ['id' => $receta->id]);
        $this->assertDatabaseMissing('ingredientes', ['id' => $ingrediente->id]);
        $this->assertDatabaseMissing('comentarios', ['id' => $comentario->id]);
        $this->assertDatabaseMissing('likes', ['receta_id' => $receta->id]);
    }

    public function test_busqueda_combinada_funciona_correctamente(): void
    {
        $user = User::factory()->create();

        // Crear receta objetivo
        $receta1 = Receta::factory()->create([
            'user_id' => $user->id,
            'titulo' => 'Tortilla Especial',
            'descripcion' => 'Con ingredientes selectos',
        ]);
        Ingrediente::factory()->create([
            'receta_id' => $receta1->id,
            'nombre' => 'Huevos Organicos',
        ]);
        $receta1->usuariosQueLesGusto()->attach(User::factory()->count(3)->create()->pluck('id'));

        // Crear receta que no cumple todos los criterios
        $receta2 = Receta::factory()->create([
            'user_id' => $user->id,
            'titulo' => 'Otra Tortilla',
            'descripcion' => 'Normal',
        ]);
        Ingrediente::factory()->create([
            'receta_id' => $receta2->id,
            'nombre' => 'Huevos',
        ]);
        $receta2->usuariosQueLesGusto()->attach($user->id); // Solo 1 like

        // Buscar con filtros combinados
        $response = $this->actingAs($user)->getJson(
            '/api/recetas?q=tortilla&ingrediente=huevo&min_likes=2'
        );

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.id', $receta1->id);
    }

    public function test_usuario_puede_interactuar_con_recetas_de_otros(): void
    {
        $autor = User::factory()->create();
        $lector = User::factory()->create();

        $receta = Receta::factory()->create(['user_id' => $autor->id]);

        // Lector puede ver
        $response = $this->actingAs($lector)->getJson("/api/recetas/{$receta->id}");
        $response->assertStatus(200);

        // Lector puede dar like
        $response = $this->actingAs($lector)->postJson("/api/recetas/{$receta->id}/like");
        $response->assertStatus(200);

        // Lector puede comentar
        $response = $this->actingAs($lector)->postJson("/api/recetas/{$receta->id}/comentarios", [
            'texto' => 'Excelente receta!',
        ]);
        $response->assertStatus(201);

        // Lector NO puede modificar
        $response = $this->actingAs($lector)->putJson("/api/recetas/{$receta->id}", [
            'titulo' => 'Intento de modificacion',
        ]);
        $response->assertStatus(403);

        // Lector NO puede eliminar
        $response = $this->actingAs($lector)->deleteJson("/api/recetas/{$receta->id}");
        $response->assertStatus(403);
    }

    public function test_listado_incluye_contador_de_likes(): void
    {
        $user = User::factory()->create();

        $receta1 = Receta::factory()->create(['user_id' => $user->id, 'titulo' => 'Receta 1']);
        $receta2 = Receta::factory()->create(['user_id' => $user->id, 'titulo' => 'Receta 2']);

        // Dar likes
        $receta1->usuariosQueLesGusto()->attach(User::factory()->count(3)->create()->pluck('id'));
        $receta2->usuariosQueLesGusto()->attach($user->id);

        $response = $this->actingAs($user)->getJson('/api/recetas');

        $response->assertStatus(200);

        // Verificar que ambas recetas tienen el contador
        $recetas = $response->json('data');
        $this->assertArrayHasKey('likes_count', $recetas[0]);
        $this->assertArrayHasKey('likes_count', $recetas[1]);
    }

    public function test_detalle_receta_incluye_todas_las_relaciones(): void
    {
        $user = User::factory()->create();
        $receta = Receta::factory()->create(['user_id' => $user->id]);

        // Crear relaciones
        Ingrediente::factory()->count(3)->create(['receta_id' => $receta->id]);
        Comentario::factory()->count(2)->create([
            'receta_id' => $receta->id,
            'user_id' => $user->id,
        ]);
        $receta->usuariosQueLesGusto()->attach(User::factory()->count(5)->create()->pluck('id'));

        $response = $this->actingAs($user)->getJson("/api/recetas/{$receta->id}");

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'id',
                'titulo',
                'descripcion',
                'instrucciones',
                'ingredientes' => [
                    '*' => ['id', 'nombre', 'cantidad', 'unidad'],
                ],
                'comentarios' => [
                    '*' => ['id', 'texto', 'user'],
                ],
                'likes_count',
            ],
        ]);

        $response->assertJsonCount(3, 'data.ingredientes');
        $response->assertJsonCount(2, 'data.comentarios');
        $response->assertJsonPath('data.likes_count', 5);
    }

    public function test_paginacion_respeta_filtros(): void
    {
        $user = User::factory()->create();

        // Crear 15 recetas con "Tortilla" en el titulo
        Receta::factory()->count(15)->create([
            'user_id' => $user->id,
            'titulo' => 'Tortilla Variante',
        ]);

        // Crear 5 recetas sin "Tortilla"
        Receta::factory()->count(5)->create([
            'user_id' => $user->id,
            'titulo' => 'Otra Receta',
        ]);

        $response = $this->actingAs($user)->getJson('/api/recetas?q=tortilla&per_page=10');

        $response->assertStatus(200);
        $response->assertJsonCount(10, 'data');
        $response->assertJsonPath('meta.total', 15);
        $response->assertJsonPath('meta.current_page', 1);
        $response->assertJsonPath('meta.last_page', 2);
    }
}
