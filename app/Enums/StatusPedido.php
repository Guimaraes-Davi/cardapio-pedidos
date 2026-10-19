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

    public function proximo(): ?self
    {
        return match ($this) {
            self::Recebido => self::EmPreparo,
            self::EmPreparo => self::SaiuParaEntrega,
            self::SaiuParaEntrega => self::Entregue,
            self::Entregue, self::Cancelado => null,
        };
    }

    public function finalizado(): bool
    {
        return $this->proximo() === null;
    }

    /**
     * Um pedido só anda uma casa para frente na sequência,
     * ou é cancelado enquanto ainda não foi finalizado.
     */
    public function podeMudarPara(self $novo): bool
    {
        if ($this->finalizado()) {
            return false;
        }

        return $novo === $this->proximo() || $novo === self::Cancelado;
    }
}
