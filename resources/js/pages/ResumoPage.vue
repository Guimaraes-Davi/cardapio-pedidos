<script setup>
import { onMounted, ref } from 'vue';
import { api } from '../api';
import { formatarReais } from '../formatos';
import CardResumo from '../components/CardResumo.vue';
import EstadoCarregamento from '../components/EstadoCarregamento.vue';

const resumo = ref(null);
const carregando = ref(true);
const erro = ref('');

async function carregar() {
    carregando.value = true;
    erro.value = '';

    try {
        resumo.value = (await api('/relatorios/resumo')).data;
    } catch (e) {
        erro.value = e.message;
    } finally {
        carregando.value = false;
    }
}

onMounted(carregar);
</script>

<template>
    <div>
        <h1 class="mb-1 text-2xl font-semibold">Resumo de vendas</h1>
        <p class="mb-4 text-sm text-stone-500">Considera apenas pedidos entregues.</p>

        <EstadoCarregamento :carregando="carregando" :erro="erro" @tentar-novamente="carregar">
            <div v-if="resumo" class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                <CardResumo
                    titulo="Faturamento hoje"
                    :valor="formatarReais(resumo.dia.faturamento_centavos)"
                    :detalhe="`${resumo.dia.pedidos} pedidos entregues`"
                />
                <CardResumo
                    titulo="Faturamento no mês"
                    :valor="formatarReais(resumo.mes.faturamento_centavos)"
                    :detalhe="`${resumo.mes.pedidos} pedidos entregues`"
                />
                <CardResumo
                    titulo="Ticket médio (mês)"
                    :valor="formatarReais(resumo.mes.ticket_medio_centavos)"
                    :detalhe="`Hoje: ${formatarReais(resumo.dia.ticket_medio_centavos)}`"
                />
                <CardResumo titulo="Pedidos entregues hoje" :valor="String(resumo.dia.pedidos)" />
            </div>

            <section v-if="resumo" class="mt-6 rounded-lg border border-stone-200 bg-white p-4">
                <h2 class="mb-3 font-semibold">Top 5 produtos do mês</h2>
                <p v-if="!resumo.top_produtos.length" class="text-sm text-stone-500">Nenhuma venda no mês ainda.</p>
                <ol v-else class="divide-y divide-stone-100">
                    <li
                        v-for="(produto, posicao) in resumo.top_produtos"
                        :key="produto.produto_id"
                        class="flex items-center justify-between gap-4 py-2 text-sm"
                    >
                        <span><span class="mr-2 text-stone-400">{{ posicao + 1 }}º</span>{{ produto.nome }}</span>
                        <span class="text-stone-500">
                            {{ produto.quantidade_vendida }} un. · {{ formatarReais(produto.faturamento_centavos) }}
                        </span>
                    </li>
                </ol>
            </section>
        </EstadoCarregamento>
    </div>
</template>
