<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\Pedido
 */
class PedidoResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'proximo_status' => $this->status->proximo()?->value,
            'cliente' => new ClienteResource($this->whenLoaded('cliente')),
            'itens' => PedidoItemResource::collection($this->whenLoaded('itens')),
            'subtotal_centavos' => $this->subtotal_centavos,
            'taxa_entrega_centavos' => $this->taxa_entrega_centavos,
            'total_centavos' => $this->total_centavos,
            'observacao' => $this->observacao,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
