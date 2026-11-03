<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Icon from '@/Components/Icon.vue';
import RuangKepala from '@modules/Ruang/Components/RuangKepala.vue';
import LampiranSoal from '../Components/LampiranSoal.vue';
import { formUbah } from '@/lib/formUbah';

const props = defineProps({ ruang: Object, tugas: Object, soal: Object, jumlahKumpul: Number, pilih: Object, siswa: Array });
const form = formUbah(() => props.pilih, (p) => ({ nilai: p?.nilai ?? '', komentar: p?.komentar ?? '' }));
const simpan = () => form.post(route('tugas.nilai', [props.ruang.id, props.tugas.id, props.pilih.id]));
</script>

<template>
    <Head :title="soal.judul" />
    <AppLayout>
        <RuangKepala :ruang="ruang" />
        <div class="grid g-utama">
            <div>
                <div class="kartu">
                    <LampiranSoal :soal="soal" />
                    <div style="margin-top:14px"><Link class="btn garis kecil" :href="route('tugas.edit', [ruang.id, tugas.id])"><Icon name="edit" :size="14" />Ubah tugas</Link></div>
                </div>
                <div v-if="pilih" id="nilai" class="kartu">
                    <h2><Icon name="file" :size="18" />Jawaban {{ pilih.nama }}</h2>
                    <p class="kecil-redup">Dikumpulkan {{ pilih.dikumpulkan }} <span v-if="pilih.terlambat" class="lencana merah">Terlambat</span></p>
                    <div v-if="pilih.teks_html" class="catatan" style="border-left-color:var(--tinta);margin-bottom:10px;font-size:14.5px" v-html="pilih.teks_html" />
                    <a v-if="pilih.file_nama" class="lampiran" :href="route('berkas.pengumpulan', [ruang.id, pilih.id])" target="_blank"><span class="ikon"><Icon name="file" :size="18" /></span><b class="potong" style="flex:1">{{ pilih.file_nama }}</b><Icon name="eye" :size="18" /></a>
                    <form class="mt" @submit.prevent="simpan">
                        <div class="baris-form">
                            <div class="field" style="max-width:140px"><label>Nilai (0-100)</label><input v-model="form.nilai" type="number" name="nilai" min="0" max="100" required autofocus></div>
                        </div>
                        <div class="field"><label>Komentar untuk siswa (opsional)</label><textarea v-model="form.komentar" name="komentar" style="min-height:80px" /></div>
                        <button type="submit" class="btn" :disabled="form.processing"><Icon name="check" :size="16" />Simpan nilai &amp; lanjut</button>
                    </form>
                </div>
            </div>
            <div class="kartu rapat">
                <h2><Icon name="users" :size="18" />Pengumpulan <span class="kanan kecil-redup">{{ jumlahKumpul }}/{{ siswa.length }}</span></h2>
                <ul class="daftar" style="padding:6px 20px 12px">
                    <li v-for="s in siswa" :key="s.id" :style="pilih && pilih.siswa_id === s.id ? 'background:var(--stabilo-lembut);margin:0 -20px;padding-left:20px;padding-right:20px' : ''">
                        <span class="teks"><b class="potong"><Link v-if="s.kumpul" :href="route('tugas.show', { ruang: ruang.id, tugas: tugas.id, siswa: s.id }) + '#nilai'">{{ s.nama }}</Link><template v-else>{{ s.nama }}</template></b>
                            <small>{{ s.waktu }}</small></span>
                        <span :class="['lencana', s.lencana[0]]">{{ s.lencana[1] }}</span>
                    </li>
                </ul>
            </div>
        </div>
    </AppLayout>
</template>
