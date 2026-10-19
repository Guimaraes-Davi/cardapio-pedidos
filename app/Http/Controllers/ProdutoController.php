<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProdutoRequest;
use App\Http\Resources\ProdutoResource;
use App\Models\PedidoItem;
use App\Models\Produto;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class ProdutoController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return ProdutoResource::collection(Produto::with('categoria')->orderBy('nome')->get());
    }

    public function store(ProdutoRequest $request): ProdutoResource
    {
        return new ProdutoResource(Produto::create($request->validated())->load('categoria'));
    }

    public function show(Produto $produto): ProdutoResource
    {
        return new ProdutoResource($produto->load('categoria'));
    }

    public function update(ProdutoRequest $request, Produto $produto): ProdutoResource
    {
        $produto->update($request->validated());

        return new ProdutoResource($produto->load('categoria'));
    }

    public function destroy(Produto $produto): Response
    {
        abort_if(
            PedidoItem::where('produto_id', $produto->id)->exists(),
            Response::HTTP_CONFLICT,
            'O produto já foi vendido e faz parte do histórico. Desative-o em vez de excluir.'
        );

        $produto->delete();

        return response()->noContent();
    }
}
