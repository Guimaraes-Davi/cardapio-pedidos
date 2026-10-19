<?php

namespace Database\Seeders;

use App\Enums\StatusPedido;
use App\Models\Cliente;
use App\Models\Pedido;
use App\Models\Produto;
use Illuminate\Database\Seeder;

class PedidoSeeder extends Seeder
{
    public function run(): void
    {
        $produtos = Produto::all();
        $clientes = Cliente::factory(20)->create();

        for ($i = 0; $i < 60; $i++) {
            // os 8 primeiros são de hoje, para a fila de pedidos e o resumo do dia terem dados
            $deHoje = $i < 8;

            $criadoEm = $deHoje
                ? fake()->dateTimeBetween('today', 'now')
                : fake()->dateTimeBetween('-30 days', 'yesterday 23:59');

            // pedidos antigos já terminaram; os de hoje podem estar em qualquer etapa
            $status = $deHoje
                ? fake()->randomElement(StatusPedido::cases())
                : fake()->randomElement([...array_fill(0, 6, StatusPedido::Entregue), StatusPedido::Cancelado]);

            $itens = $produtos->random(fake()->numberBetween(1, 4))->map(fn (Produto $produto) => [
                'produto_id' => $produto->id,
                'quantidade' => fake()->numberBetween(1, 3),
                'preco_unitario_centavos' => $produto->preco_centavos,
            ]);

            $subtotal = $itens->sum(fn (array $item) => $item['quantidade'] * $item['preco_unitario_centavos']);
            $taxa = fake()->randomElement([0, 500, 799, 990]);

            $pedido = Pedido::factory()
                ->for($clientes->random())
                ->create([
                    'status' => $status,
                    'subtotal_centavos' => $subtotal,
                    'taxa_entrega_centavos' => $taxa,
                    'total_centavos' => $subtotal + $taxa,
                    'observacao' => fake()->optional(0.2)->randomElement(['Sem cebola', 'Troco para R$ 100', 'Interfone quebrado, ligar ao chegar']),
                    'created_at' => $criadoEm,
                    'updated_at' => $criadoEm,
                ]);

            $pedido->itens()->createMany($itens);
        }
    }
}
