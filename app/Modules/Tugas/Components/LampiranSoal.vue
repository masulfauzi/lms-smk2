<script setup>
import Icon from '@/Components/Icon.vue';

// Pengganti tugas::_soal: judul, tenggat, instruksi, dan lampiran soal
defineProps({ soal: { type: Object, required: true } });
</script>

<template>
    <div class="remah">Tugas · tenggat {{ soal.tenggat }}</div>
    <h1 style="font-size:23px;margin-bottom:14px"><span class="sorot">{{ soal.judul }}</span></h1>
    <div v-if="soal.instruksi_html" class="materi" v-html="soal.instruksi_html" />
    <div v-else class="materi"><p class="kecil-redup">Tidak ada instruksi tertulis.</p></div>
    <a v-if="soal.lampiran_nama" class="lampiran" :href="soal.url_lampiran" target="_blank"><span class="ikon"><Icon name="file" :size="20" /></span>
        <span style="flex:1;min-width:0"><b class="potong" style="display:block">{{ soal.lampiran_nama }}</b><small class="kecil-redup">Lampiran soal</small></span><Icon name="download" :size="18" /></a>
</template>
