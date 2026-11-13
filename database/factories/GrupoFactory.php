<?php

namespace Database\Factories;

use App\Models\Grupo;
use App\Models\Curso;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Grupo>
 */
class GrupoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
    return [
        'numeroCiclo' => fake()->numberBetween(1, 3),
        'ano' => 2026,
        'numeroGrupo' => fake()->numberBetween(1, 20),
        'abierto' => false,
        'curso_id' => Curso::factory(),
    ];
    }
}
