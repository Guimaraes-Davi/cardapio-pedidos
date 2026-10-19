<?php

namespace App\Models;

use App\Enums\StatusPedido;
use Database\Factories\PedidoFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'cliente_id',
    'status',
    'subtotal_centavos',
    'taxa_entrega_centavos',
    'total_centavos',
    'observacao',
])]
class Pedido extends Model
{
    /** @use HasFactory<PedidoFactory> */
    use HasFactory;

    protected $attributes = [
        'status' => 'recebido',
    ];

    protected function casts(): array
    {
        return [
            'status' => StatusPedido::class,
            'subtotal_centavos' => 'integer',
            'taxa_entrega_centavos' => 'integer',
            'total_centavos' => 'integer',
        ];
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    public function itens(): HasMany
    {
        return $this->hasMany(PedidoItem::class);
    }
}
