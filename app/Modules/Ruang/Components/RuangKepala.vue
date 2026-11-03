<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import Icon from '@/Components/Icon.vue';

// Header + tab ruang belajar; dipakai halaman modul Ruang, Pertemuan, Tugas, Kuis, Diskusi.
// Prop `ruang` berasal dari Ruang::kepala().
const props = defineProps({ ruang: { type: Object, required: true } });
const guru = computed(() => usePage().props.auth.user.role !== 'siswa');
const on = (...nama) => (nama.some((n) => route().current(n)) ? 'on' : '');
const id = computed(() => props.ruang.id);
</script>

<template>
    <div class="ruang-kepala">
        <div class="isi">
            <span class="pita" :style="{ background: ruang.warna }" />
            <div style="flex:1;min-width:220px">
                <div class="remah"><Link :href="route('ruang')">Ruang Belajar</Link> / {{ ruang.kelas }}</div>
                <h1>{{ ruang.mapel }}</h1>
                <div class="meta">
                    <span><Icon name="grid" :size="15" />{{ ruang.kelas }}</span>
                    <span><Icon name="user" :size="15" />{{ ruang.guru ?? 'Guru belum ditentukan' }}</span>
                    <span><Icon name="flag" :size="15" />KKM {{ ruang.kkm }}</span>
                </div>
            </div>
            <div v-if="guru" class="aksi" style="display:flex;gap:8px;flex-wrap:wrap">
                <Link class="btn" :href="route('pertemuan.create', id)"><Icon name="plus" :size="16" />Pertemuan</Link>
                <Link class="btn garis" :href="route('tugas.create', id)"><Icon name="clipboard" :size="16" />Tugas</Link>
                <Link class="btn garis" :href="route('kuis.create', id)"><Icon name="quiz" :size="16" />Kuis</Link>
            </div>
        </div>
        <nav class="tab">
            <Link :href="route('ruang.show', id)" :class="on('ruang.show', 'pertemuan.*')"><Icon name="book" :size="16" />Materi</Link>
            <Link :href="route('tugas.index', id)" :class="on('tugas.*')"><Icon name="clipboard" :size="16" />Tugas</Link>
            <Link :href="route('kuis.index', id)" :class="on('kuis.*')"><Icon name="quiz" :size="16" />Kuis</Link>
            <Link :href="route('ruang.presensi', id)" :class="on('ruang.presensi')"><Icon name="check-square" :size="16" />Presensi</Link>
            <Link :href="route('ruang.nilai', id)" :class="on('ruang.nilai')"><Icon name="award" :size="16" />Nilai</Link>
            <Link :href="route('ruang.diskusi', id)" :class="on('ruang.diskusi')"><Icon name="message" :size="16" />Diskusi</Link>
            <Link :href="route('ruang.anggota', id)" :class="on('ruang.anggota')"><Icon name="users" :size="16" />Anggota</Link>
            <Link v-if="guru" :href="route('ruang.pengaturan', id)" :class="on('ruang.pengaturan')"><Icon name="settings" :size="16" />Pengaturan</Link>
        </nav>
    </div>
</template>
