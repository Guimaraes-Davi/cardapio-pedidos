<script setup>
const links = [
    { to: '/', label: 'Cardápio' },
    { to: '/pedidos', label: 'Pedidos' },
    { to: '/resumo', label: 'Resumo' },
];
</script>

<template>
    <header class="border-b border-stone-200 bg-white">
        <nav class="mx-auto flex max-w-6xl flex-wrap items-center gap-x-6 gap-y-2 px-4 py-3">
            <span class="text-lg font-semibold text-orange-600">Cardápio &amp; Pedidos</span>
            <div class="flex gap-1">
                <RouterLink
                    v-for="link in links"
                    :key="link.to"
                    :to="link.to"
                    class="rounded-md px-3 py-1.5 text-sm font-medium text-stone-600 hover:bg-stone-100"
                    exact-active-class="bg-orange-50 !text-orange-700"
                >
                    {{ link.label }}
                </RouterLink>
            </div>
        </nav>
    </header>

    <main class="mx-auto max-w-6xl px-4 py-6">
        <RouterView />
    </main>
</template>
