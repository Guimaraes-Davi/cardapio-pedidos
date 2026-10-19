<?php

namespace Tests\Feature;

use App\Enums\StatusPedido;
use App\Models\Pedido;
use App\Models\PedidoItem;
use App\Models\Produto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class RelatorioResumoTest extends TestCase
{
    use RefreshDatabase;

    private function pedido(StatusPedido $status, int $total, string $data): Pedido
    {
        return Pedido::factory()->status($status)->create([
            'total_centavos' => $total,
            'created_at' => Carbon::parse($data),
        ]);
    }

    private function item(Pedido $pedido, Produto $produto, int $quantidade): void
    {
        PedidoItem::factory()->for($pedido)->for($produto)->create([
            'quantidade' => $quantidade,
            'preco_unitario_centavos' => $produto->preco_centavos,
        ]);
    }

    public function test_resumo_conta_apenas_pedidos_entregues_no_periodo(): void
    {
        $this->travelTo(Carbon::parse('2026-10-15 20:00:00'));

        $pizza = Produto::factory()->create(['nome' => 'Pizza', 'preco_centavos' => 5000]);
        $burger = Produto::factory()->create(['nome' => 'Burger', 'preco_centavos' => 2500]);
        $suco = Produto::factory()->create(['nome' => 'Suco', 'preco_centavos' => 1000]);

        // hoje
        $hoje1 = $this->pedido(StatusPedido::Entregue, 3000, '2026-10-15 12:00');
        $hoje2 = $this->pedido(StatusPedido::Entregue, 5000, '2026-10-15 19:30');
        $cancelado = $this->pedido(StatusPedido::Cancelado, 9000, '2026-10-15 13:00');
        $this->pedido(StatusPedido::EmPreparo, 4000, '2026-10-15 19:50');

        // mais cedo no mês
        $comecoDoMes = $this->pedido(StatusPedido::Entregue, 10000, '2026-10-01 00:30');

        // mês passado: fora do resumo do mês
        $mesPassado = $this->pedido(StatusPedido::Entregue, 7000, '2026-09-30 23:30');

        $this->item($hoje1, $pizza, 2);
        $this->item($hoje2, $pizza, 3);
        $this->item($hoje2, $burger, 1);
        $this->item($comecoDoMes, $burger, 2);
        $this->item($comecoDoMes, $suco, 1);
        $this->item($cancelado, $suco, 10);
        $this->item($mesPassado, $burger, 20);

        $this->getJson('/api/relatorios/resumo')
            ->assertOk()
            ->assertExactJson(['data' => [
                'dia' => [
                    'faturamento_centavos' => 8000,
                    'pedidos' => 2,
                    'ticket_medio_centavos' => 4000,
                ],
                'mes' => [
                    'faturamento_centavos' => 18000,
                    'pedidos' => 3,
                    'ticket_medio_centavos' => 6000,
                ],
                'top_produtos' => [
                    ['produto_id' => $pizza->id, 'nome' => 'Pizza', 'quantidade_vendida' => 5, 'faturamento_centavos' => 25000],
                    ['produto_id' => $burger->id, 'nome' => 'Burger', 'quantidade_vendida' => 3, 'faturamento_centavos' => 7500],
                    ['produto_id' => $suco->id, 'nome' => 'Suco', 'quantidade_vendida' => 1, 'faturamento_centavos' => 1000],
                ],
            ]]);
    }

    public function test_resumo_sem_vendas_retorna_zeros(): void
    {
        $this->getJson('/api/relatorios/resumo')
            ->assertOk()
            ->assertJsonPath('data.dia.faturamento_centavos', 0)
            ->assertJsonPath('data.mes.ticket_medio_centavos', 0)
            ->assertJsonPath('data.top_produtos', []);
    }
}
