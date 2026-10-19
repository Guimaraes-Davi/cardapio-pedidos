<?php

namespace Tests\Feature;

use App\Enums\StatusPedido;
use App\Models\Pedido;
use App\Models\Produto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CriarPedidoTest extends TestCase
{
    use RefreshDatabase;

    private function payload(array $itens): array
    {
        return [
            'cliente' => ['nome' => 'Maria Souza', 'telefone' => '11988887777'],
            'observacao' => 'Sem cebola',
            'itens' => $itens,
        ];
    }

    public function test_calcula_subtotal_e_total_com_o_preco_do_banco(): void
    {
        config(['cardapio.taxa_entrega_centavos' => 799]);
        $burger = Produto::factory()->create(['preco_centavos' => 2490]);
        $refri = Produto::factory()->create(['preco_centavos' => 690]);

        $resposta = $this->postJson('/api/pedidos', [
            ...$this->payload([
                ['produto_id' => $burger->id, 'quantidade' => 2, 'preco_unitario_centavos' => 1],
                ['produto_id' => $refri->id, 'quantidade' => 3],
            ]),
            'total_centavos' => 1,
        ]);

        // 2 x 2490 + 3 x 690 = 7050; + 799 de entrega = 7849
        $resposta->assertCreated()
            ->assertJsonPath('data.status', StatusPedido::Recebido->value)
            ->assertJsonPath('data.subtotal_centavos', 7050)
            ->assertJsonPath('data.taxa_entrega_centavos', 799)
            ->assertJsonPath('data.total_centavos', 7849)
            ->assertJsonCount(2, 'data.itens');

        $this->assertDatabaseHas('pedidos', ['subtotal_centavos' => 7050, 'total_centavos' => 7849]);
        $this->assertDatabaseHas('pedido_itens', [
            'produto_id' => $burger->id,
            'quantidade' => 2,
            'preco_unitario_centavos' => 2490,
        ]);
        $this->assertDatabaseHas('clientes', ['telefone' => '11988887777', 'nome' => 'Maria Souza']);
    }

    public function test_preco_do_item_fica_congelado_mesmo_se_o_produto_mudar_de_preco(): void
    {
        $produto = Produto::factory()->create(['preco_centavos' => 3000]);

        $this->postJson('/api/pedidos', $this->payload([
            ['produto_id' => $produto->id, 'quantidade' => 1],
        ]))->assertCreated();

        $produto->update(['preco_centavos' => 9999]);

        $this->assertSame(3000, Pedido::first()->itens->first()->preco_unitario_centavos);
    }

    public function test_pedido_sem_itens_responde_422(): void
    {
        $this->postJson('/api/pedidos', $this->payload([]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['itens']);

        $this->assertDatabaseCount('pedidos', 0);
    }

    public function test_pedido_com_produto_inexistente_responde_422(): void
    {
        $this->postJson('/api/pedidos', $this->payload([
            ['produto_id' => 999, 'quantidade' => 1],
        ]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['itens.0.produto_id']);

        $this->assertDatabaseCount('pedidos', 0);
    }

    public function test_pedido_com_produto_inativo_responde_422(): void
    {
        $produto = Produto::factory()->inativo()->create();

        $this->postJson('/api/pedidos', $this->payload([
            ['produto_id' => $produto->id, 'quantidade' => 1],
        ]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['itens.0.produto_id']);
    }
}
