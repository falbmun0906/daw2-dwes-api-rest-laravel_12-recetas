<?php

namespace Database\Factories;

use App\Models\Receta;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Ingrediente>
 */
class IngredienteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $unidades = ['g', 'ml', 'ud', 'cucharadas', 'tazas', 'kg', 'l'];

        return [
            'receta_id' => Receta::factory(),
            'nombre' => fake()->word(),
            'cantidad' => fake()->numberBetween(1, 500),
            'unidad' => fake()->randomElement($unidades),
        ];
    }
}
