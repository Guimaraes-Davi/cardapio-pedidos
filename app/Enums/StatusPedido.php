<?php

namespace App\Enums;

enum StatusPedido: string
{
    case Recebido = 'recebido';
    case EmPreparo = 'em_preparo';
    case SaiuParaEntrega = 'saiu_para_entrega';
    case Entregue = 'entregue';
    case Cancelado = 'cancelado';

    public function label(): string
    {
        return match ($this) {
            self::Recebido => 'Recebido',
            self::EmPreparo => 'Em preparo',
            self::SaiuParaEntrega => 'Saiu para entrega',
            self::Entregue => 'Entregue',
            self::Cancelado => 'Cancelado',
        };
    }
}
