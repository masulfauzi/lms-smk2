<script setup>
import TombolHapus from '@/Components/TombolHapus.vue';

// Satu pesan diskusi (topik atau balasan); slot default untuk daftar balasan
defineProps({ pesan: Object, urlHapus: String, konfirmasi: String, kecil: Boolean });
</script>

<template>
    <div class="pesan">
        <span :class="['avatar kecil', pesan.bukan_siswa ? 'guru' : '']">{{ pesan.inisial }}</span>
        <div class="isi">
            <div class="nama">{{ pesan.nama }} <span v-if="pesan.guru" class="lencana kuning">Guru</span> <small>{{ pesan.waktu }}</small>
                <span v-if="pesan.boleh_hapus" style="margin-left:auto"><TombolHapus :url="urlHapus" :pesan="konfirmasi" /></span></div>
            <div :style="kecil ? 'font-size:14px;margin-top:2px' : 'font-size:14.5px;margin-top:4px'" v-html="pesan.isi_html" />
            <slot />
        </div>
    </div>
</template>
