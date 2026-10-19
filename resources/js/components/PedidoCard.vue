<script setup>
import { computed } from 'vue';
import { STATUS, formatarDataHora, formatarReais } from '../formatos';

const props = defineProps({
    pedido: { type: Object, required: true },
    atualizando: { type: Boolean, default: false },
});

const emit = defineEmits(['mudar-status']);

const finalizado = computed(() => props.pedido.proximo_status === null);
</script>

<template>
    <article class="rounded-lg border border-stone-200 bg-white p-4">
        <header class="flex flex-wrap items-start justify-between gap-2">
            <div>
                <h3 class="font-semibold">Pedido #{{ pedido.id }}</h3>
                <p class="text-sm text-stone-500">
                    {{ pedido.cliente.nome }} · {{ pedido.cliente.telefone }} · {{ formatarDataHora(pedido.created_at) }}
                </p>
            </div>
            <span class="rounded-full px-2.5 py-0.5 text-xs font-medium" :class="STATUS[pedido.status].cor">
                {{ pedido.status_label }}
            </span>
        </header>

        <ul class="mt-3 space-y-1 text-sm">
            <li v-for="item in pedido.itens" :key="item.id" class="flex justify-between gap-4">
                <span>{{ item.quantidade }}× {{ item.produto.nome }}</span>
                <span class="text-stone-500">{{ formatarReais(item.total_centavos) }}</span>
            </li>
        </ul>

        <p v-if="pedido.observacao" class="mt-2 text-sm text-amber-700">Obs.: {{ pedido.observacao }}</p>

        <footer class="mt-3 flex flex-wrap items-center justify-between gap-3 border-t border-stone-100 pt-3">
            <span class="text-sm">
                Total <strong>{{ formatarReais(pedido.total_centavos) }}</strong>
                <span class="ml-1 text-stone-400">(entrega {{ formatarReais(pedido.taxa_entrega_centavos) }})</span>
            </span>

            <div v-if="!finalizado" class="flex gap-2">
                <button
                    type="button"
                    class="rounded-md border border-stone-300 px-3 py-1.5 text-sm text-stone-600 hover:bg-stone-100 disabled:opacity-50"
                    :disabled="atualizando"
                    @click="emit('mudar-status', pedido, 'cancelado')"
                >
                    Cancelar
                </button>
                <button
                    type="button"
                    class="rounded-md bg-orange-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-orange-700 disabled:opacity-50"
                    :disabled="atualizando"
                    @click="emit('mudar-status', pedido, pedido.proximo_status)"
                >
                    {{ atualizando ? 'Salvando…' : `Avançar para ${STATUS[pedido.proximo_status].label}` }}
                </button>
            </div>
        </footer>
    </article>
</template>
