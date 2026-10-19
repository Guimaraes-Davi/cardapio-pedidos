<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProdutoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        // PATCH atualiza só os campos enviados; POST e PUT exigem o produto completo
        $obrigatorio = $this->isMethod('PATCH') ? 'sometimes' : 'required';

        return [
            'categoria_id' => [$obrigatorio, 'integer', 'exists:categorias,id'],
            'nome' => [$obrigatorio, 'string', 'max:150'],
            'descricao' => ['nullable', 'string', 'max:1000'],
            'preco_centavos' => [$obrigatorio, 'integer', 'min:1'],
            'ativo' => ['sometimes', 'boolean'],
        ];
    }
}
