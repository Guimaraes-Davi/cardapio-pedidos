<?php

namespace App\Http\Controllers;

use App\Http\Requests\CategoriaRequest;
use App\Http\Resources\CategoriaResource;
use App\Models\Categoria;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class CategoriaController extends Controller
{
    /**
     * Lista as categorias já com os produtos ativos: é exatamente o que o cardápio precisa.
     */
    public function index(): AnonymousResourceCollection
    {
        $categorias = Categoria::query()
            ->with(['produtos' => fn ($query) => $query->where('ativo', true)->orderBy('nome')])
            ->orderBy('id')
            ->get();

        return CategoriaResource::collection($categorias);
    }

    public function store(CategoriaRequest $request): CategoriaResource
    {
        return new CategoriaResource(Categoria::create($request->validated()));
    }

    public function show(Categoria $categoria): CategoriaResource
    {
        return new CategoriaResource($categoria->load('produtos'));
    }

    public function update(CategoriaRequest $request, Categoria $categoria): CategoriaResource
    {
        $categoria->update($request->validated());

        return new CategoriaResource($categoria);
    }

    public function destroy(Categoria $categoria): Response
    {
        abort_if(
            $categoria->produtos()->exists(),
            Response::HTTP_CONFLICT,
            'A categoria possui produtos. Mova ou exclua os produtos antes.'
        );

        $categoria->delete();

        return response()->noContent();
    }
}
