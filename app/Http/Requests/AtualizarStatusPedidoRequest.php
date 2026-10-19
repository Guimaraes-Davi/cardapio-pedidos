<?php

namespace App\Http\Requests;

use App\Enums\StatusPedido;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class AtualizarStatusPedidoRequest extends FormRequest
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
        return [
            'status' => ['required', Rule::enum(StatusPedido::class)],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'status.required' => 'Informe o novo status.',
            'status.enum' => 'Status inválido. Valores aceitos: '.implode(', ', array_column(StatusPedido::cases(), 'value')).'.',
        ];
    }

    /**
     * Regra de negócio que depende do estado atual do pedido,
     * por isso roda depois das regras básicas.
     *
     * @return array<int, callable>
     */
    public function after(): array
    {
        return [
            function (Validator $validator) {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                $atual = $this->route('pedido')->status;
                $novo = $this->enum('status', StatusPedido::class);

                if ($atual->podeMudarPara($novo)) {
                    return;
                }

                $permitidos = $atual->finalizado()
                    ? 'nenhum, o pedido já foi finalizado'
                    : "\"{$atual->proximo()->label()}\" ou \"Cancelado\"";

                $validator->errors()->add(
                    'status',
                    "Não é possível mudar o pedido de \"{$atual->label()}\" para \"{$novo->label()}\". Permitido: {$permitidos}."
                );
            },
        ];
    }
}
