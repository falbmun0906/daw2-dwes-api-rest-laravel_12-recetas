<?php

namespace Database\Seeders;

use App\Models\Comentario;
use App\Models\Ingrediente;
use App\Models\Receta;
use App\Models\User;
use Illuminate\Database\Seeder;

class RecetaCompleteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Asegurarse que existan usuarios
        $admin = User::where('email', 'admin@demo.local')->first();
        $user = User::where('email', 'user@demo.local')->first();

        if (!$admin || !$user) {
            $this->command->info('Por favor ejecuta UserSeeder primero');
            return;
        }

        // Receta 1: Tortilla de Patatas
        $tortilla = Receta::create([
            'user_id' => $admin->id,
            'titulo' => 'Tortilla de Patatas',
            'descripcion' => 'La clásica tortilla española, perfecta para cualquier ocasión',
            'instrucciones' => '1. Pelar y cortar las patatas en rodajas finas. 2. Freír las patatas en abundante aceite. 3. Batir los huevos. 4. Mezclar las patatas escurridas con los huevos. 5. Cuajar en la sartén por ambos lados.',
            'publicada' => true,
        ]);

        // Ingredientes de tortilla
        Ingrediente::create(['receta_id' => $tortilla->id, 'nombre' => 'Huevos', 'cantidad' => '4', 'unidad' => 'ud']);
        Ingrediente::create(['receta_id' => $tortilla->id, 'nombre' => 'Patatas', 'cantidad' => '500', 'unidad' => 'g']);
        Ingrediente::create(['receta_id' => $tortilla->id, 'nombre' => 'Aceite de oliva', 'cantidad' => '150', 'unidad' => 'ml']);
        Ingrediente::create(['receta_id' => $tortilla->id, 'nombre' => 'Sal', 'cantidad' => '1', 'unidad' => 'cucharadita']);

        // Likes y comentarios
        $tortilla->likes()->attach([$admin->id, $user->id]);
        Comentario::create([
            'receta_id' => $tortilla->id,
            'user_id' => $user->id,
            'texto' => '¡La mejor tortilla que he probado! Quedó perfecta.',
        ]);

        // Receta 2: Paella Valenciana
        $paella = Receta::create([
            'user_id' => $user->id,
            'titulo' => 'Paella Valenciana',
            'descripcion' => 'Auténtica paella valenciana con pollo, conejo y verduras',
            'instrucciones' => '1. Sofreír el pollo y el conejo. 2. Añadir las verduras. 3. Agregar el arroz. 4. Cubrir con caldo y cocinar a fuego medio. 5. Dejar reposar antes de servir.',
            'publicada' => true,
        ]);

        Ingrediente::create(['receta_id' => $paella->id, 'nombre' => 'Arroz', 'cantidad' => '400', 'unidad' => 'g']);
        Ingrediente::create(['receta_id' => $paella->id, 'nombre' => 'Pollo', 'cantidad' => '300', 'unidad' => 'g']);
        Ingrediente::create(['receta_id' => $paella->id, 'nombre' => 'Conejo', 'cantidad' => '300', 'unidad' => 'g']);
        Ingrediente::create(['receta_id' => $paella->id, 'nombre' => 'Garrofón', 'cantidad' => '100', 'unidad' => 'g']);
        Ingrediente::create(['receta_id' => $paella->id, 'nombre' => 'Judías verdes', 'cantidad' => '100', 'unidad' => 'g']);
        Ingrediente::create(['receta_id' => $paella->id, 'nombre' => 'Tomate', 'cantidad' => '2', 'unidad' => 'ud']);
        Ingrediente::create(['receta_id' => $paella->id, 'nombre' => 'Azafrán', 'cantidad' => '1', 'unidad' => 'pizca']);

        $paella->likes()->attach($admin->id);
        Comentario::create([
            'receta_id' => $paella->id,
            'user_id' => $admin->id,
            'texto' => 'Auténtica paella valenciana. ¡Excelente!',
        ]);

        // Receta 3: Gazpacho Andaluz
        $gazpacho = Receta::create([
            'user_id' => $admin->id,
            'titulo' => 'Gazpacho Andaluz',
            'descripcion' => 'Sopa fría de verduras, perfecta para el verano',
            'instrucciones' => '1. Triturar todos los ingredientes. 2. Añadir aceite mientras se tritura. 3. Agregar vinagre al gusto. 4. Refrigerar mínimo 2 horas. 5. Servir bien frío.',
            'publicada' => true,
        ]);

        Ingrediente::create(['receta_id' => $gazpacho->id, 'nombre' => 'Tomates', 'cantidad' => '1', 'unidad' => 'kg']);
        Ingrediente::create(['receta_id' => $gazpacho->id, 'nombre' => 'Pepino', 'cantidad' => '1', 'unidad' => 'ud']);
        Ingrediente::create(['receta_id' => $gazpacho->id, 'nombre' => 'Pimiento verde', 'cantidad' => '1', 'unidad' => 'ud']);
        Ingrediente::create(['receta_id' => $gazpacho->id, 'nombre' => 'Ajo', 'cantidad' => '1', 'unidad' => 'diente']);
        Ingrediente::create(['receta_id' => $gazpacho->id, 'nombre' => 'Pan duro', 'cantidad' => '50', 'unidad' => 'g']);
        Ingrediente::create(['receta_id' => $gazpacho->id, 'nombre' => 'Aceite de oliva', 'cantidad' => '50', 'unidad' => 'ml']);
        Ingrediente::create(['receta_id' => $gazpacho->id, 'nombre' => 'Vinagre', 'cantidad' => '2', 'unidad' => 'cucharadas']);

        $gazpacho->likes()->attach([$admin->id, $user->id]);
        Comentario::create([
            'receta_id' => $gazpacho->id,
            'user_id' => $user->id,
            'texto' => 'Perfecto para el verano. Muy refrescante.',
        ]);

        // Receta 4: Tarta de Santiago
        $tarta = Receta::create([
            'user_id' => $user->id,
            'titulo' => 'Tarta de Santiago',
            'descripcion' => 'Delicioso postre gallego a base de almendras',
            'instrucciones' => '1. Batir los huevos con el azúcar. 2. Añadir la almendra molida. 3. Agregar ralladura de limón. 4. Hornear a 180°C durante 30 minutos. 5. Dejar enfriar y espolvorear azúcar glas.',
            'publicada' => true,
        ]);

        Ingrediente::create(['receta_id' => $tarta->id, 'nombre' => 'Almendra molida', 'cantidad' => '250', 'unidad' => 'g']);
        Ingrediente::create(['receta_id' => $tarta->id, 'nombre' => 'Azúcar', 'cantidad' => '250', 'unidad' => 'g']);
        Ingrediente::create(['receta_id' => $tarta->id, 'nombre' => 'Huevos', 'cantidad' => '4', 'unidad' => 'ud']);
        Ingrediente::create(['receta_id' => $tarta->id, 'nombre' => 'Ralladura de limón', 'cantidad' => '1', 'unidad' => 'ud']);
        Ingrediente::create(['receta_id' => $tarta->id, 'nombre' => 'Azúcar glas', 'cantidad' => '50', 'unidad' => 'g']);

        $tarta->likes()->attach($admin->id);
        Comentario::create([
            'receta_id' => $tarta->id,
            'user_id' => $admin->id,
            'texto' => 'Postre espectacular. Muy fácil de hacer.',
        ]);
        Comentario::create([
            'receta_id' => $tarta->id,
            'user_id' => $user->id,
            'texto' => 'La receta de mi abuela. ¡Gracias por compartirla!',
        ]);

        $this->command->info('✅ Recetas de ejemplo creadas con ingredientes, likes y comentarios');
    }
}
