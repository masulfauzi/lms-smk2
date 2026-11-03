<script setup>
import { Link } from '@inertiajs/vue3';
import Icon from '@/Components/Icon.vue';

defineProps({ pengumuman: { type: Array, required: true } });
</script>

<template>
    <div class="kartu">
        <h2><Icon name="megaphone" :size="18" />Pengumuman <Link class="kanan" :href="route('pengumuman')">Semua</Link></h2>
        <div v-for="p in pengumuman" :key="p.id" style="padding:10px 0;border-bottom:1px dashed var(--garis-2)">
            <div class="tebal" style="display:flex;gap:6px;align-items:center"><Icon v-if="p.disematkan" name="pin" :size="14" />{{ p.judul }}</div>
            <div class="kecil-redup">{{ p.penulis }} · {{ p.waktu }}</div>
            <div style="font-size:14px;margin-top:4px">{{ p.ringkas }}</div>
        </div>
        <p v-if="!pengumuman.length" class="kecil-redup">Belum ada pengumuman.</p>
    </div>
</template>
