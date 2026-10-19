<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pedidos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained();
            // string em vez de enum do banco: funciona igual no SQLite e no MySQL,
            // e os valores válidos ficam no enum App\Enums\StatusPedido
            $table->string('status', 30)->default('recebido')->index();
            $table->unsignedInteger('subtotal_centavos');
            $table->unsignedInteger('taxa_entrega_centavos')->default(0);
            $table->unsignedInteger('total_centavos');
            $table->text('observacao')->nullable();
            $table->timestamps();

            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pedidos');
    }
};
