<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\Produto
 */
class ProdutoResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'categoria_id' => $this->categoria_id,
            'nome' => $this->nome,
            'descricao' => $this->descricao,
            'preco_centavos' => $this->preco_centavos,
            'ativo' => $this->ativo,
            'categoria' => new CategoriaResource($this->whenLoaded('categoria')),
        ];
    }
}
