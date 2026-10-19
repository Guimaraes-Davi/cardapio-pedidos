<script setup>
import { ref, watch } from 'vue';
import { api } from '../api';
import { STATUS } from '../formatos';
import EstadoCarregamento from '../components/EstadoCarregamento.vue';
import PedidoCard from '../components/PedidoCard.vue';

const statusFiltro = ref('');
const pedidos = ref([]);
const meta = ref(null);
const carregando = ref(true);
const erro = ref('');
const erroAcao = ref('');
const atualizandoId = ref(null);

let ultimaRequisicao = 0;

async function carregar(pagina = 1) {
    // se o filtro mudar rápido, só a resposta da última requisição é aplicada
    const requisicao = ++ultimaRequisicao;
    carregando.value = true;
    erro.value = '';

    const parametros = new URLSearchParams({ page: pagina, per_page: 10 });
    if (statusFiltro.value) {
        parametros.set('status', statusFiltro.value);
    }

    try {
        const resposta = await api(`/pedidos?${parametros}`);
        if (requisicao !== ultimaRequisicao) return;
        pedidos.value = resposta.data;
        meta.value = resposta.meta;
    } catch (e) {
        if (requisicao !== ultimaRequisicao) return;
        erro.value = e.message;
    } finally {
        if (requisicao === ultimaRequisicao) carregando.value = false;
    }
}

async function mudarStatus(pedido, status) {
    erroAcao.value = '';
    atualizandoId.value = pedido.id;

    try {
        const { data } = await api(`/pedidos/${pedido.id}/status`, { method: 'PATCH', body: { status } });

        if (statusFiltro.value && data.status !== statusFiltro.value) {
            await carregar(meta.value.current_page);
        } else {
            pedidos.value = pedidos.value.map((p) => (p.id === data.id ? data : p));
        }
    } catch (e) {
        erroAcao.value = `Pedido #${pedido.id}: ${e.message}`;
    } finally {
        atualizandoId.value = null;
    }
}

watch(statusFiltro, () => carregar(1), { immediate: true });
</script>

<template>
    <div>
        <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
            <h1 class="text-2xl font-semibold">Pedidos</h1>
            <label class="flex items-center gap-2 text-sm">
                Status
                <select v-model="statusFiltro" class="rounded-md border border-stone-300 bg-white px-3 py-2">
                    <option value="">Todos</option>
                    <option v-for="(status, valor) in STATUS" :key="valor" :value="valor">{{ status.label }}</option>
                </select>
            </label>
        </div>

        <p v-if="erroAcao" class="mb-4 rounded-md bg-red-50 p-3 text-sm text-red-800">{{ erroAcao }}</p>

        <EstadoCarregamento :carregando="carregando" :erro="erro" @tentar-novamente="carregar(meta?.current_page ?? 1)">
            <p v-if="!pedidos.length" class="py-10 text-center text-stone-500">Nenhum pedido encontrado.</p>

            <div class="grid gap-3 md:grid-cols-2">
                <PedidoCard
                    v-for="pedido in pedidos"
                    :key="pedido.id"
                    :pedido="pedido"
                    :atualizando="atualizandoId === pedido.id"
                    @mudar-status="mudarStatus"
                />
            </div>

            <nav v-if="meta && meta.last_page > 1" class="mt-6 flex items-center justify-center gap-3 text-sm">
                <button
                    type="button"
                    class="rounded-md border border-stone-300 px-3 py-1.5 disabled:opacity-40"
                    :disabled="meta.current_page === 1"
                    @click="carregar(meta.current_page - 1)"
                >
                    Anterior
                </button>
                <span>Página {{ meta.current_page }} de {{ meta.last_page }} · {{ meta.total }} pedidos</span>
                <button
                    type="button"
                    class="rounded-md border border-stone-300 px-3 py-1.5 disabled:opacity-40"
                    :disabled="meta.current_page === meta.last_page"
                    @click="carregar(meta.current_page + 1)"
                >
                    Próxima
                </button>
            </nav>
        </EstadoCarregamento>
    </div>
</template>
