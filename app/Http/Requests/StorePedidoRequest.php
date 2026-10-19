<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePedidoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * O front envia só produto_id e quantidade. Preço e total são
     * calculados no servidor, por isso não fazem parte das regras.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'cliente.nome' => ['required', 'string', 'max:150'],
            'cliente.telefone' => ['required', 'string', 'max:20'],
            'observacao' => ['nullable', 'string', 'max:500'],
            'itens' => ['required', 'array', 'min:1'],
            'itens.*.produto_id' => [
                'required',
                'integer',
                'distinct',
                Rule::exists('produtos', 'id')->where('ativo', true),
            ],
            'itens.*.quantidade' => ['required', 'integer', 'min:1', 'max:50'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'itens.required' => 'O pedido precisa ter pelo menos um item.',
            'itens.min' => 'O pedido precisa ter pelo menos um item.',
            'itens.*.produto_id.exists' => 'O produto :input não existe ou não está disponível.',
            'itens.*.produto_id.distinct' => 'O mesmo produto aparece mais de uma vez; ajuste a quantidade.',
            'itens.*.quantidade.min' => 'A quantidade mínima é 1.',
            'itens.*.quantidade.max' => 'A quantidade máxima por item é 50.',
            'cliente.nome.required' => 'Informe o nome do cliente.',
            'cliente.telefone.required' => 'Informe o telefone do cliente.',
        ];
    }
}
