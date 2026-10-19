<script setup>
import { formatarReais } from '../formatos';

defineProps({
    produto: { type: Object, required: true },
    quantidadeNoCarrinho: { type: Number, default: 0 },
});

const emit = defineEmits(['adicionar']);
</script>

<template>
    <article class="flex flex-col justify-between gap-3 rounded-lg border border-stone-200 bg-white p-4">
        <div>
            <h3 class="font-semibold">{{ produto.nome }}</h3>
            <p class="mt-1 text-sm text-stone-500">{{ produto.descricao }}</p>
        </div>
        <div class="flex items-center justify-between">
            <span class="font-semibold text-stone-900">{{ formatarReais(produto.preco_centavos) }}</span>
            <button
                type="button"
                class="rounded-md bg-orange-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-orange-700"
                @click="emit('adicionar', produto)"
            >
                Adicionar
                <span v-if="quantidadeNoCarrinho" class="ml-1 rounded bg-white/25 px-1.5">{{ quantidadeNoCarrinho }}</span>
            </button>
        </div>
    </article>
</template>
