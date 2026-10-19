<?php

namespace App\Http\Controllers;

use App\Actions\CriarPedido;
use App\Enums\StatusPedido;
use App\Http\Requests\AtualizarStatusPedidoRequest;
use App\Http\Requests\ListarPedidosRequest;
use App\Http\Requests\StorePedidoRequest;
use App\Http\Resources\PedidoResource;
use App\Models\Pedido;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;

class PedidoController extends Controller
{
    public function index(ListarPedidosRequest $request): AnonymousResourceCollection
    {
        $pedidos = Pedido::query()
            ->with(['cliente', 'itens.produto'])
            ->when($request->validated('status'), fn ($query, $status) => $query->where('status', $status))
            ->when($request->validated('data_inicio'), fn ($query, $data) => $query->where('created_at', '>=', Carbon::parse($data)->startOfDay()))
            ->when($request->validated('data_fim'), fn ($query, $data) => $query->where('created_at', '<=', Carbon::parse($data)->endOfDay()))
            ->latest()
            ->paginate($request->integer('per_page', 15))
            ->withQueryString();

        return PedidoResource::collection($pedidos);
    }

    public function store(StorePedidoRequest $request, CriarPedido $criarPedido): JsonResponse
    {
        $pedido = $criarPedido->handle($request->validated());

        return (new PedidoResource($pedido))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function atualizarStatus(AtualizarStatusPedidoRequest $request, Pedido $pedido): PedidoResource
    {
        $pedido->update(['status' => $request->enum('status', StatusPedido::class)]);

        return new PedidoResource($pedido->load(['cliente', 'itens.produto']));
    }
}
