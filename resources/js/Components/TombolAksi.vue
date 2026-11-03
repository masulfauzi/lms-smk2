<script setup>
import { router } from '@inertiajs/vue3';

// Tombol yang mengirim POST/DELETE (pengganti <form method="post"> kecil), dengan konfirmasi opsional
const props = defineProps({
    href: { type: String, required: true },
    method: { type: String, default: 'post' },
    konfirmasi: String,
    data: { type: Object, default: () => ({}) },
    kelas: { type: String, default: 'btn' },
    title: String,
});

function kirim() {
    if (props.konfirmasi && !confirm(props.konfirmasi)) return;
    router.visit(props.href, { method: props.method, data: props.data, preserveScroll: true });
}
</script>

<template>
    <button type="button" :class="kelas" :title="title" @click="kirim"><slot /></button>
</template>
