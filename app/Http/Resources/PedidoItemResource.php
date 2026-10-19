<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\PedidoItem
 */
class PedidoItemResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'produto_id' => $this->produto_id,
            'produto' => new ProdutoResource($this->whenLoaded('produto')),
            'quantidade' => $this->quantidade,
            'preco_unitario_centavos' => $this->preco_unitario_centavos,
            'total_centavos' => $this->quantidade * $this->preco_unitario_centavos,
        ];
    }
}
