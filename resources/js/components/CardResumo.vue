<script setup>
defineProps({
    titulo: { type: String, required: true },
    valor: { type: String, required: true },
    detalhe: { type: String, default: '' },
});
</script>

<template>
    <div class="rounded-lg border border-stone-200 bg-white p-4">
        <p class="text-sm text-stone-500">{{ titulo }}</p>
        <p class="mt-1 text-2xl font-semibold text-stone-900">{{ valor }}</p>
        <p v-if="detalhe" class="mt-1 text-xs text-stone-400">{{ detalhe }}</p>
    </div>
</template>
