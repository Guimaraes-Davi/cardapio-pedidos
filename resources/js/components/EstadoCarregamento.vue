<script setup>
defineProps({
    carregando: { type: Boolean, default: false },
    erro: { type: String, default: '' },
});

const emit = defineEmits(['tentar-novamente']);
</script>

<template>
    <p v-if="carregando" class="py-10 text-center text-stone-500">Carregando…</p>
    <div v-else-if="erro" class="rounded-lg border border-red-200 bg-red-50 p-4 text-red-800">
        <p>{{ erro }}</p>
        <button type="button" class="mt-2 text-sm font-medium underline" @click="emit('tentar-novamente')">
            Tentar novamente
        </button>
    </div>
    <slot v-else />
</template>
