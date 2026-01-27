<?php

namespace Database\Factories;

use App\Models\Receta;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Comentario>
 */
class ComentarioFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'receta_id' => Receta::factory(),
            'user_id' => User::factory(),
            'texto' => fake()->paragraph(),
        ];
    }
}
