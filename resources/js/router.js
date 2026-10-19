import { createRouter, createWebHistory } from 'vue-router';
import CardapioPage from './pages/CardapioPage.vue';
import PedidosPage from './pages/PedidosPage.vue';
import ResumoPage from './pages/ResumoPage.vue';

export default createRouter({
    history: createWebHistory(),
    routes: [
        { path: '/', name: 'cardapio', component: CardapioPage },
        { path: '/pedidos', name: 'pedidos', component: PedidosPage },
        { path: '/resumo', name: 'resumo', component: ResumoPage },
        { path: '/:pathMatch(.*)*', redirect: '/' },
    ],
});
