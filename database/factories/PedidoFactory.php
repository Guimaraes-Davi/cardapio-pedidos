<?php

namespace Database\Factories;

use App\Enums\StatusPedido;
use App\Models\Cliente;
use App\Models\Pedido;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Pedido>
 */
class PedidoFactory extends Factory
{
    public function definition(): array
    {
        $subtotal = fake()->numberBetween(2000, 15000);
        $taxa = fake()->randomElement([0, 500, 799, 990]);

        return [
            'cliente_id' => Cliente::factory(),
            'status' => StatusPedido::Recebido,
            'subtotal_centavos' => $subtotal,
            'taxa_entrega_centavos' => $taxa,
            'total_centavos' => $subtotal + $taxa,
            'observacao' => null,
        ];
    }

    public function status(StatusPedido $status): static
    {
        return $this->state(['status' => $status]);
    }
}
