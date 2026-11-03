<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Icon from '@/Components/Icon.vue';
import RuangKepala from '@modules/Ruang/Components/RuangKepala.vue';
import LampiranSoal from '../Components/LampiranSoal.vue';

const props = defineProps({ ruang: Object, tugas: Object, soal: Object, jenisKumpul: String, bisaDikumpulkan: Boolean, lewatTenggat: Boolean, milikSaya: Object });
const form = useForm({ teks: props.milikSaya?.teks ?? '', berkas: null });
const kumpul = () => form.post(route('tugas.kumpul', [props.ruang.id, props.tugas.id]), {
    forceFormData: true, preserveScroll: true, onSuccess: () => { form.berkas = null; },
});
</script>

<template>
    <Head :title="soal.judul" />
    <AppLayout>
        <RuangKepala :ruang="ruang" />
        <div class="grid g-utama">
            <div class="kartu"><LampiranSoal :soal="soal" /></div>
            <div>
                <div v-if="milikSaya && milikSaya.nilai !== null" class="kartu">
                    <div class="nilai-besar"><span>Nilai kamu</span><b>{{ milikSaya.nilai }}</b><span>{{ milikSaya.tuntas ? 'Tuntas' : 'Belum tuntas' }} · KKM {{ ruang.kkm }}</span></div>
                    <div v-if="milikSaya.komentar_html" class="catatan"><div class="tulisan" style="font-size:18px;color:var(--margin)">Komentar guru</div><div style="font-size:14px" v-html="milikSaya.komentar_html" /></div>
                </div>
                <div class="kartu">
                    <h2><Icon name="upload" :size="18" />{{ milikSaya ? 'Pengumpulan kamu' : 'Kumpulkan tugas' }}</h2>
                    <template v-if="milikSaya">
                        <p class="kecil-redup">Dikumpulkan {{ milikSaya.dikumpulkan }} <span v-if="milikSaya.terlambat" class="lencana merah">Terlambat</span></p>
                        <div v-if="milikSaya.teks_html" class="catatan" style="border-left-color:var(--tinta);margin-bottom:10px;font-size:14px" v-html="milikSaya.teks_html" />
                        <a v-if="milikSaya.file_nama" class="lampiran" :href="route('berkas.pengumpulan', [ruang.id, milikSaya.id])" target="_blank"><span class="ikon"><Icon name="file" :size="18" /></span><b class="potong" style="flex:1">{{ milikSaya.file_nama }}</b></a>
                    </template>
                    <template v-if="!milikSaya || milikSaya.nilai === null">
                        <template v-if="bisaDikumpulkan">
                            <p v-if="lewatTenggat" class="flash error" style="margin-top:12px">Tenggat sudah lewat. Pengumpulan akan ditandai terlambat.</p>
                            <form class="mt" @submit.prevent="kumpul">
                                <div v-if="jenisKumpul !== 'file'" class="field"><label>Jawaban teks</label><textarea v-model="form.teks" name="teks" placeholder="Tulis jawabanmu di sini..." /></div>
                                <div v-if="jenisKumpul !== 'teks'" class="field"><label>{{ milikSaya?.file_nama ? 'Ganti file' : 'File jawaban' }} (maks. 20 MB)</label><input type="file" name="berkas" @input="form.berkas = $event.target.files[0]"><small>PDF, Word, Excel, PowerPoint, foto (JPG/PNG), atau ZIP.</small></div>
                                <button type="submit" class="btn blok" :disabled="form.processing"><Icon name="upload" :size="16" />{{ milikSaya ? 'Kumpulkan ulang' : 'Kumpulkan' }}</button>
                            </form>
                        </template>
                        <p v-else class="flash error" style="margin-top:12px">Tenggat sudah lewat dan guru tidak menerima pengumpulan terlambat.</p>
                    </template>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
