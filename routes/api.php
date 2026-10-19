<?php

use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\PedidoController;
use App\Http\Controllers\ProdutoController;
use App\Http\Controllers\RelatorioController;
use Illuminate\Support\Facades\Route;

Route::apiResource('categorias', CategoriaController::class);
Route::apiResource('produtos', ProdutoController::class);

Route::get('pedidos', [PedidoController::class, 'index']);
Route::post('pedidos', [PedidoController::class, 'store']);
Route::patch('pedidos/{pedido}/status', [PedidoController::class, 'atualizarStatus']);

Route::get('relatorios/resumo', [RelatorioController::class, 'resumo']);
