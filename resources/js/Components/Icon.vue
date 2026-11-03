<script setup>
import ikon from '@/lib/ikon';

defineProps({ name: { type: String, required: true }, size: { type: Number, default: 20 } });
</script>

<template>
    <svg class="ic" :width="size" :height="size" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9"
        stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" v-html="ikon[name] ?? ''" />
</template>
