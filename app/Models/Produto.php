<?php

namespace App\Models;

use Database\Factories\ProdutoFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['categoria_id', 'nome', 'descricao', 'preco_centavos', 'ativo'])]
class Produto extends Model
{
    /** @use HasFactory<ProdutoFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'preco_centavos' => 'integer',
            'ativo' => 'boolean',
        ];
    }

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class);
    }
}
