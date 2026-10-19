<script setup>
import { computed, onMounted, reactive, ref } from 'vue';
import { api, ErroApi } from '../api';
import { formatarReais } from '../formatos';
import EstadoCarregamento from '../components/EstadoCarregamento.vue';
import ProdutoCard from '../components/ProdutoCard.vue';

const categorias = ref([]);
const carregando = ref(true);
const erro = ref('');

const carrinho = ref([]);
const cliente = reactive({ nome: '', telefone: '' });
const observacao = ref('');
const enviando = ref(false);
const errosEnvio = ref([]);
const pedidoCriado = ref(null);

// só uma prévia: o valor que vale é o que o servidor devolve ao criar o pedido
const totalItens = computed(() =>
    carrinho.value.reduce((soma, item) => soma + item.produto.preco_centavos * item.quantidade, 0),
);

const quantidadeNoCarrinho = (produtoId) =>
    carrinho.value.find((item) => item.produto.id === produtoId)?.quantidade ?? 0;

async function carregarCardapio() {
    carregando.value = true;
    erro.value = '';

    try {
        categorias.value = (await api('/categorias')).data;
    } catch (e) {
        erro.value = e.message;
    } finally {
        carregando.value = false;
    }
}

function adicionar(produto) {
    pedidoCriado.value = null;
    const item = carrinho.value.find((i) => i.produto.id === produto.id);

    if (item) {
        item.quantidade++;
    } else {
        carrinho.value.push({ produto, quantidade: 1 });
    }
}

function alterarQuantidade(item, delta) {
    item.quantidade += delta;

    if (item.quantidade <= 0) {
        carrinho.value = carrinho.value.filter((i) => i !== item);
    }
}

async function finalizarPedido() {
    enviando.value = true;
    errosEnvio.value = [];

    try {
        const resposta = await api('/pedidos', {
            method: 'POST',
            body: {
                cliente: { ...cliente },
                observacao: observacao.value || null,
                itens: carrinho.value.map((item) => ({
                    produto_id: item.produto.id,
                    quantidade: item.quantidade,
                })),
            },
        });

        pedidoCriado.value = resposta.data;
        carrinho.value = [];
        observacao.value = '';
    } catch (e) {
        errosEnvio.value = e instanceof ErroApi && e.status === 422 ? Object.values(e.erros).flat() : [e.message];
    } finally {
        enviando.value = false;
    }
}

onMounted(carregarCardapio);
</script>

<template>
    <div class="grid gap-6 lg:grid-cols-3">
        <section class="lg:col-span-2">
            <h1 class="mb-4 text-2xl font-semibold">Cardápio</h1>

            <EstadoCarregamento :carregando="carregando" :erro="erro" @tentar-novamente="carregarCardapio">
                <div v-for="categoria in categorias" :key="categoria.id" class="mb-8">
                    <h2 class="mb-3 text-lg font-semibold text-stone-700">{{ categoria.nome }}</h2>
                    <div class="grid gap-3 sm:grid-cols-2">
                        <ProdutoCard
                            v-for="produto in categoria.produtos"
                            :key="produto.id"
                            :produto="produto"
                            :quantidade-no-carrinho="quantidadeNoCarrinho(produto.id)"
                            @adicionar="adicionar"
                        />
                    </div>
                </div>
            </EstadoCarregamento>
        </section>

        <aside class="h-fit rounded-lg border border-stone-200 bg-white p-4 lg:sticky lg:top-6">
            <h2 class="text-lg font-semibold">Carrinho</h2>

            <div
                v-if="pedidoCriado"
                class="mt-3 rounded-md border border-emerald-200 bg-emerald-50 p-3 text-sm text-emerald-800"
            >
                Pedido <strong>#{{ pedidoCriado.id }}</strong> recebido! Total com entrega:
                <strong>{{ formatarReais(pedidoCriado.total_centavos) }}</strong>.
            </div>

            <p v-if="!carrinho.length" class="mt-3 text-sm text-stone-500">Seu carrinho está vazio.</p>

            <ul v-else class="mt-3 divide-y divide-stone-100">
                <li v-for="item in carrinho" :key="item.produto.id" class="flex items-center justify-between gap-2 py-2">
                    <div class="text-sm">
                        <p class="font-medium">{{ item.produto.nome }}</p>
                        <p class="text-stone-500">{{ formatarReais(item.produto.preco_centavos * item.quantidade) }}</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <button
                            type="button"
                            class="size-7 rounded border border-stone-300 hover:bg-stone-100"
                            :aria-label="`Remover um ${item.produto.nome}`"
                            @click="alterarQuantidade(item, -1)"
                        >−</button>
                        <span class="w-5 text-center text-sm">{{ item.quantidade }}</span>
                        <button
                            type="button"
                            class="size-7 rounded border border-stone-300 hover:bg-stone-100"
                            :aria-label="`Adicionar um ${item.produto.nome}`"
                            @click="alterarQuantidade(item, 1)"
                        >+</button>
                    </div>
                </li>
            </ul>

            <form v-if="carrinho.length" class="mt-4 space-y-3 border-t border-stone-100 pt-4" @submit.prevent="finalizarPedido">
                <p class="flex justify-between font-semibold">
                    <span>Total dos itens</span>
                    <span>{{ formatarReais(totalItens) }}</span>
                </p>
                <p class="text-xs text-stone-500">A taxa de entrega é somada ao confirmar o pedido.</p>

                <label class="block text-sm">
                    Nome
                    <input v-model.trim="cliente.nome" required class="mt-1 w-full rounded-md border border-stone-300 px-3 py-2" />
                </label>
                <label class="block text-sm">
                    Telefone
                    <input
                        v-model.trim="cliente.telefone"
                        required
                        type="tel"
                        class="mt-1 w-full rounded-md border border-stone-300 px-3 py-2"
                    />
                </label>
                <label class="block text-sm">
                    Observação
                    <textarea v-model="observacao" rows="2" class="mt-1 w-full rounded-md border border-stone-300 px-3 py-2" />
                </label>

                <ul v-if="errosEnvio.length" class="rounded-md bg-red-50 p-3 text-sm text-red-800">
                    <li v-for="mensagem in errosEnvio" :key="mensagem">{{ mensagem }}</li>
                </ul>

                <button
                    type="submit"
                    class="w-full rounded-md bg-orange-600 py-2.5 font-medium text-white hover:bg-orange-700 disabled:opacity-50"
                    :disabled="enviando"
                >
                    {{ enviando ? 'Enviando…' : 'Finalizar pedido' }}
                </button>
            </form>
        </aside>
    </div>
</template>
