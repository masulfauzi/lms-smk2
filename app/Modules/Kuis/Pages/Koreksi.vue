<script setup>
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import { formUbah } from '@/lib/formUbah';
import AppLayout from '@/Layouts/AppLayout.vue';
import Icon from '@/Components/Icon.vue';
import RuangKepala from '@modules/Ruang/Components/RuangKepala.vue';

const props = defineProps({ ruang: Object, kuis: Object, percobaan: Object, soal: Array });
const adaEsai = computed(() => props.soal.some((s) => s.jenis === 'esai'));
// Setelah simpan, server membuka koreksi siswa berikutnya di halaman yang sama: isi ulang skor dari props
const form = formUbah(() => props.percobaan.id, () => ({ skor: Object.fromEntries(props.soal.filter((s) => s.jenis === 'esai').map((s) => [s.id, s.skor])) }));
const simpan = () => form.post(route('kuis.koreksi', [props.ruang.id, props.kuis.id, props.percobaan.id]));
</script>

<template>
    <Head :title="'Koreksi · ' + percobaan.nama" />
    <AppLayout>
        <RuangKepala :ruang="ruang" />
        <div class="kepala">
            <div><div class="remah"><Link :href="route('kuis.show', [ruang.id, kuis.id])">{{ kuis.judul }}</Link> / koreksi</div><h1><span class="sorot">{{ percobaan.nama }}</span></h1>
                <p>{{ percobaan.keterangan }}</p></div>
        </div>
        <form class="kartu" @submit.prevent="simpan">
            <div v-for="s in soal" :key="s.id" style="padding:16px 0;border-bottom:1px dashed var(--garis-2)">
                <div class="tebal" style="white-space:pre-line">{{ s.urutan }}. {{ s.pertanyaan }}</div>
                <div v-if="s.jenis === 'pg'" style="margin-top:6px;font-size:14px">Jawaban: <b>{{ s.jawaban_pg }}</b> <span :class="['lencana', s.benar ? 'hijau' : 'merah']">{{ s.benar ? 'Benar' : 'Salah · kunci ' + s.kunci }}</span> <span class="kecil-redup">bobot {{ s.bobot }}</span></div>
                <template v-else>
                    <div v-if="s.jawaban_html" class="catatan" style="border-left-color:var(--tinta);margin:8px 0;font-size:14.5px" v-html="s.jawaban_html" />
                    <div v-else class="catatan" style="border-left-color:var(--tinta);margin:8px 0;font-size:14.5px"><em class="kecil-redup">Tidak dijawab</em></div>
                    <div class="inline-form"><div class="field"><label>Skor (0–{{ s.bobot }})</label>
                        <input v-model="form.skor[s.id]" type="number" :name="`skor[${s.id}]`" min="0" :max="s.bobot" step="0.5" style="max-width:120px" required></div></div>
                </template>
            </div>
            <button v-if="adaEsai" type="submit" class="btn mt" :disabled="form.processing"><Icon name="check" :size="16" />Simpan koreksi</button>
        </form>
    </AppLayout>
</template>
