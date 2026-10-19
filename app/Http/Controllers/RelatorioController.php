<?php

namespace App\Http\Controllers;

use App\Enums\StatusPedido;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class RelatorioController extends Controller
{
    /**
     * Só pedidos entregues contam como faturamento: cancelados nunca
     * geraram receita e os em andamento ainda podem ser cancelados.
     */
    public function resumo(): JsonResource
    {
        $inicioMes = now()->startOfMonth();
        $fimMes = now()->endOfMonth();

        return JsonResource::make([
            'dia' => $this->totaisEntregues(now()->startOfDay(), now()->endOfDay()),
            'mes' => $this->totaisEntregues($inicioMes, $fimMes),
            'top_produtos' => $this->produtosMaisVendidos($inicioMes, $fimMes),
        ]);
    }

    /**
     * @return array{faturamento_centavos: int, pedidos: int, ticket_medio_centavos: int}
     */
    private function totaisEntregues(Carbon $inicio, Carbon $fim): array
    {
        $totais = DB::table('pedidos')
            ->where('status', StatusPedido::Entregue->value)
            ->whereBetween('created_at', [$inicio, $fim])
            ->selectRaw('COUNT(*) as pedidos, COALESCE(SUM(total_centavos), 0) as faturamento')
            ->first();

        $pedidos = (int) $totais->pedidos;
        $faturamento = (int) $totais->faturamento;

        return [
            'faturamento_centavos' => $faturamento,
            'pedidos' => $pedidos,
            'ticket_medio_centavos' => $pedidos > 0 ? (int) round($faturamento / $pedidos) : 0,
        ];
    }

    private function produtosMaisVendidos(Carbon $inicio, Carbon $fim): Collection
    {
        return DB::table('pedido_itens')
            ->join('pedidos', 'pedidos.id', '=', 'pedido_itens.pedido_id')
            ->join('produtos', 'produtos.id', '=', 'pedido_itens.produto_id')
            ->where('pedidos.status', StatusPedido::Entregue->value)
            ->whereBetween('pedidos.created_at', [$inicio, $fim])
            ->groupBy('produtos.id', 'produtos.nome')
            ->select('produtos.id', 'produtos.nome')
            ->selectRaw('SUM(pedido_itens.quantidade) as quantidade_vendida')
            ->selectRaw('SUM(pedido_itens.quantidade * pedido_itens.preco_unitario_centavos) as faturamento_centavos')
            ->orderByDesc('quantidade_vendida')
            ->orderBy('produtos.nome')
            ->limit(5)
            ->get()
            ->map(fn (object $linha) => [
                'produto_id' => (int) $linha->id,
                'nome' => $linha->nome,
                'quantidade_vendida' => (int) $linha->quantidade_vendida,
                'faturamento_centavos' => (int) $linha->faturamento_centavos,
            ]);
    }
}
