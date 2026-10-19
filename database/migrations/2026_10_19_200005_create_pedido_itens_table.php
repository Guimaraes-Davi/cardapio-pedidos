<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pedido_itens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pedido_id')->constrained()->cascadeOnDelete();
            // restrict: um produto que já foi vendido não pode sumir do histórico
            $table->foreignId('produto_id')->constrained()->restrictOnDelete();
            $table->unsignedSmallInteger('quantidade');
            // preço congelado no momento da compra; mudar o cardápio não altera pedidos antigos
            $table->unsignedInteger('preco_unitario_centavos');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pedido_itens');
    }
};
