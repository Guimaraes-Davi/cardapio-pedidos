<?php

namespace App\Models;

use Database\Factories\ClienteFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['nome', 'telefone'])]
class Cliente extends Model
{
    /** @use HasFactory<ClienteFactory> */
    use HasFactory;

    public function pedidos(): HasMany
    {
        return $this->hasMany(Pedido::class);
    }
}
