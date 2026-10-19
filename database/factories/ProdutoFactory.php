<?php

namespace Database\Factories;

use App\Models\Categoria;
use App\Models\Produto;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Produto>
 */
class ProdutoFactory extends Factory
{
    public function definition(): array
    {
        return [
            'categoria_id' => Categoria::factory(),
            'nome' => ucfirst(fake()->words(2, true)),
            'descricao' => fake()->sentence(),
            'preco_centavos' => fake()->numberBetween(500, 6000),
            'ativo' => true,
        ];
    }

    public function inativo(): static
    {
        return $this->state(['ativo' => false]);
    }
}
