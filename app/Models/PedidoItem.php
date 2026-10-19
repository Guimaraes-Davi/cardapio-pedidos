<?php

namespace App\Models;

use Database\Factories\PedidoItemFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// o plural automático do Laravel é em inglês ("pedido_items"), então o nome da tabela é explícito
#[Table('pedido_itens')]
#[Fillable(['pedido_id', 'produto_id', 'quantidade', 'preco_unitario_centavos'])]
class PedidoItem extends Model
{
    /** @use HasFactory<PedidoItemFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'quantidade' => 'integer',
            'preco_unitario_centavos' => 'integer',
        ];
    }

    public function pedido(): BelongsTo
    {
        return $this->belongsTo(Pedido::class);
    }

    public function produto(): BelongsTo
    {
        return $this->belongsTo(Produto::class);
    }
}
