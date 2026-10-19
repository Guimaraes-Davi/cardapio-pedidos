<?php

namespace Tests\Feature;

use App\Enums\StatusPedido;
use App\Models\Pedido;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AtualizarStatusPedidoTest extends TestCase
{
    use RefreshDatabase;

    public function test_avanca_para_o_proximo_status(): void
    {
        $pedido = Pedido::factory()->status(StatusPedido::Recebido)->create();

        $this->patchJson("/api/pedidos/{$pedido->id}/status", ['status' => 'em_preparo'])
            ->assertOk()
            ->assertJsonPath('data.status', 'em_preparo')
            ->assertJsonPath('data.proximo_status', 'saiu_para_entrega');

        $this->assertSame(StatusPedido::EmPreparo, $pedido->fresh()->status);
    }

    public function test_pode_cancelar_pedido_em_andamento(): void
    {
        $pedido = Pedido::factory()->status(StatusPedido::SaiuParaEntrega)->create();

        $this->patchJson("/api/pedidos/{$pedido->id}/status", ['status' => 'cancelado'])
            ->assertOk()
            ->assertJsonPath('data.status', 'cancelado');
    }

    public function test_pular_etapa_responde_422(): void
    {
        $pedido = Pedido::factory()->status(StatusPedido::Recebido)->create();

        $this->patchJson("/api/pedidos/{$pedido->id}/status", ['status' => 'entregue'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['status' => 'Não é possível mudar o pedido de "Recebido" para "Entregue"']);

        $this->assertSame(StatusPedido::Recebido, $pedido->fresh()->status);
    }

    public function test_pedido_finalizado_nao_muda_de_status(): void
    {
        $pedido = Pedido::factory()->status(StatusPedido::Entregue)->create();

        $this->patchJson("/api/pedidos/{$pedido->id}/status", ['status' => 'cancelado'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['status']);
    }

    public function test_voltar_status_responde_422(): void
    {
        $pedido = Pedido::factory()->status(StatusPedido::EmPreparo)->create();

        $this->patchJson("/api/pedidos/{$pedido->id}/status", ['status' => 'recebido'])
            ->assertUnprocessable();
    }

    public function test_status_inexistente_responde_422(): void
    {
        $pedido = Pedido::factory()->create();

        $this->patchJson("/api/pedidos/{$pedido->id}/status", ['status' => 'voando'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['status']);
    }
}
