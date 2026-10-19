<?php

namespace App\Http\Requests;

use App\Enums\StatusPedido;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ListarPedidosRequest extends FormRequest
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
            'status' => ['nullable', Rule::enum(StatusPedido::class)],
            'data_inicio' => ['nullable', 'date_format:Y-m-d'],
            'data_fim' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:data_inicio'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'status.enum' => 'Status inválido. Valores aceitos: '.implode(', ', array_column(StatusPedido::cases(), 'value')).'.',
            'data_inicio.date_format' => 'Use o formato AAAA-MM-DD na data inicial.',
            'data_fim.date_format' => 'Use o formato AAAA-MM-DD na data final.',
            'data_fim.after_or_equal' => 'A data final não pode ser anterior à inicial.',
        ];
    }
}
