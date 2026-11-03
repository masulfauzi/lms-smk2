<script setup>
import { Link } from '@inertiajs/vue3';
import Icon from '@/Components/Icon.vue';

defineProps({ jadwal: { type: Object, required: true } });
</script>

<template>
    <div class="kartu">
        <h2><Icon name="calendar" :size="18" />Jadwal hari ini <span class="kanan kecil-redup">{{ jadwal.tanggal }}</span></h2>
        <div v-for="j in jadwal.daftar" :key="j.id" class="slot" :style="{ borderLeftColor: j.warna }">
            <div style="display:flex;justify-content:space-between;gap:8px;align-items:center">
                <b>{{ j.mapel }}</b>
                <span v-if="j.berlangsung" class="lencana hijau">Berlangsung</span>
            </div>
            <small>{{ j.jam }} · {{ j.keterangan }}</small>
            <div style="display:flex;gap:6px;margin-top:8px;flex-wrap:wrap">
                <Link class="btn kecil garis" :href="j.url_ruang"><Icon name="book" :size="14" />Buka ruang</Link>
                <a v-if="j.link_meet" :class="['btn kecil', j.berlangsung ? 'stabilo' : 'garis']" :href="j.link_meet" target="_blank" rel="noopener"><Icon name="video" :size="14" />Kelas daring</a>
            </div>
        </div>
        <p v-if="!jadwal.daftar.length" class="kecil-redup">Tidak ada jadwal hari ini.</p>
    </div>
</template>
