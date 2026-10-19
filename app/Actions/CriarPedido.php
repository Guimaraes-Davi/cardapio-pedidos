<?php

namespace App\Actions;

use App\Enums\StatusPedido;
use App\Models\Cliente;
use App\Models\Pedido;
use App\Models\Produto;
use Illuminate\Support\Facades\DB;

class CriarPedido
{
    /**
     * @param  array{cliente: array{nome: string, telefone: string}, itens: array<int, array{produto_id: int, quantidade: int}>, observacao?: string|null}  $dados
     */
    public function handle(array $dados): Pedido
    {
        $itensEnviados = collect($dados['itens']);

        // o preço vem sempre do banco; qualquer valor enviado pelo front é ignorado
        $produtos = Produto::whereIn('id', $itensEnviados->pluck('produto_id'))->get()->keyBy('id');

        $itens = $itensEnviados->map(fn (array $item) => [
            'produto_id' => $item['produto_id'],
            'quantidade' => $item['quantidade'],
            'preco_unitario_centavos' => $produtos[$item['produto_id']]->preco_centavos,
        ]);

        $subtotal = $itens->sum(fn (array $item) => $item['quantidade'] * $item['preco_unitario_centavos']);
        $taxaEntrega = config('cardapio.taxa_entrega_centavos');

        // pedido sem itens ou itens sem pedido nunca podem ficar gravados pela metade
        return DB::transaction(function () use ($dados, $itens, $subtotal, $taxaEntrega) {
            $cliente = Cliente::updateOrCreate(
                ['telefone' => $dados['cliente']['telefone']],
                ['nome' => $dados['cliente']['nome']],
            );

            $pedido = $cliente->pedidos()->create([
                'status' => StatusPedido::Recebido,
                'subtotal_centavos' => $subtotal,
                'taxa_entrega_centavos' => $taxaEntrega,
                'total_centavos' => $subtotal + $taxaEntrega,
                'observacao' => $dados['observacao'] ?? null,
            ]);

            $pedido->itens()->createMany($itens);

            return $pedido->load(['cliente', 'itens.produto']);
        });
    }
}
