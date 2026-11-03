<script setup>
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';

// Halaman error untuk navigasi Inertia (403/404/500/503); teks sama dengan resources/views/errors
const props = defineProps({ status: Number, pesan: String });
const teks = {
    403: ['Akses ditolak', 'Kamu tidak punya akses ke halaman ini.'],
    404: ['Halaman tidak ditemukan', 'Halaman yang kamu cari tidak ada atau sudah dihapus.'],
    500: ['Terjadi kesalahan', 'Ada masalah di server. Coba beberapa saat lagi.'],
    503: ['Sedang perbaikan', 'Ada masalah di server. Coba beberapa saat lagi.'],
};
const isi = computed(() => teks[props.status] ?? teks[500]);
</script>

<template>
    <Head :title="String(status)" />
    <div style="min-height:100vh;display:grid;place-items:center;padding:24px;text-align:center">
        <div>
            <div class="tulisan" style="font-size:72px;color:var(--tinta);line-height:1">{{ status }}</div>
            <h1 style="margin:10px 0 6px"><span class="sorot">{{ isi[0] }}</span></h1>
            <p style="color:var(--redup)">{{ (status === 403 && pesan) || isi[1] }}</p>
            <Link class="btn" :href="route('home')">Kembali ke beranda</Link>
        </div>
    </div>
</template>
