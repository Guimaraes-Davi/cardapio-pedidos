<?php

namespace Tests\Unit;

use App\Enums\StatusPedido;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class StatusPedidoTest extends TestCase
{
    /**
     * Todas as transições permitidas; qualquer combinação fora desta lista deve ser recusada.
     */
    private const PERMITIDAS = [
        'recebido' => ['em_preparo', 'cancelado'],
        'em_preparo' => ['saiu_para_entrega', 'cancelado'],
        'saiu_para_entrega' => ['entregue', 'cancelado'],
        'entregue' => [],
        'cancelado' => [],
    ];

    public static function todasAsCombinacoes(): iterable
    {
        foreach (StatusPedido::cases() as $atual) {
            foreach (StatusPedido::cases() as $novo) {
                yield "{$atual->value} -> {$novo->value}" => [$atual, $novo];
            }
        }
    }

    #[DataProvider('todasAsCombinacoes')]
    public function test_transicao_de_status(StatusPedido $atual, StatusPedido $novo): void
    {
        $esperado = in_array($novo->value, self::PERMITIDAS[$atual->value], true);

        $this->assertSame($esperado, $atual->podeMudarPara($novo));
    }

    public function test_entregue_e_cancelado_sao_finais(): void
    {
        $this->assertTrue(StatusPedido::Entregue->finalizado());
        $this->assertTrue(StatusPedido::Cancelado->finalizado());
        $this->assertFalse(StatusPedido::SaiuParaEntrega->finalizado());
    }
}
