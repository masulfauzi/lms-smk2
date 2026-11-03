<script setup>
import Icon from './Icon.vue';
import TombolAksi from './TombolAksi.vue';

// Pengganti partials/hapus.blade.php
defineProps({
    url: { type: String, required: true },
    pesan: { type: String, default: 'Hapus data ini?' },
    kelas: { type: String, default: 'ikon-btn' },
    label: String,
});
</script>

<template>
    <TombolAksi :href="url" method="delete" :konfirmasi="pesan" :kelas="kelas" title="Hapus" style="display:inline-flex">
        <Icon name="trash" :size="16" />{{ label }}
    </TombolAksi>
</template>
