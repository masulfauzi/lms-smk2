<script setup>
import { computed, onBeforeUnmount, onMounted, reactive, ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import axios from 'axios';
import AppLayout from '@/Layouts/AppLayout.vue';
import Icon from '@/Components/Icon.vue';

const props = defineProps({ ruang: Object, kuis: Object, batas: String, sekarangMs: Number, soal: Array });

const aktif = ref(0);
const jawaban = reactive(Object.fromEntries(props.soal.map((s) => [s.id, s.jawaban ?? (s.jenis === 'pg' ? null : '')])));
const status = ref('Jawaban tersimpan otomatis');
const sisa = ref(null);
const antre = {};            // jawaban yang gagal terkirim (sinyal hilang), dikirim ulang berkala
const timerKetik = {};
let timerUlang = null;
let timerDetak = null;
let terkumpul = false;

const terjawab = (s) => { const j = jawaban[s.id]; return j !== null && j !== undefined && String(j).trim() !== ''; };
const jumlahTerjawab = computed(() => props.soal.filter(terjawab).length);

function ke(n) {
    if (n < 0 || n >= props.soal.length) return;
    aktif.value = n;
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

const urlJawab = route('kuis.jawab', [props.ruang.id, props.kuis.id]);
function kumpulkan() {
    if (terkumpul) return;
    terkumpul = true;
    router.post(route('kuis.kumpul', [props.ruang.id, props.kuis.id]));
}

function kirim(id, nilai) {
    status.value = 'Menyimpan...';
    axios.post(urlJawab, { soal_id: id, jawaban: nilai }, { headers: { Accept: 'application/json' } })
        .then(({ data }) => {
            delete antre[id];
            status.value = data.ok ? 'Tersimpan ' + data.tersimpan : 'Gagal menyimpan';
        })
        .catch((e) => {
            if (e.response?.status === 409) { kumpulkan(); return; }
            if (e.response) { status.value = 'Gagal menyimpan'; return; }
            antre[id] = nilai;
            status.value = 'Koneksi terputus, akan dicoba lagi...';
        });
}

function pilih(s, huruf) {
    jawaban[s.id] = huruf;
    kirim(s.id, huruf);
}
function ketik(s) {
    clearTimeout(timerKetik[s.id]);
    timerKetik[s.id] = setTimeout(() => kirim(s.id, jawaban[s.id]), 900);
}

function konfirmasiKumpul() {
    const kosong = props.soal.length - jumlahTerjawab.value;
    if (confirm(kosong ? `Masih ada ${kosong} soal kosong. Tetap kumpulkan?` : 'Kumpulkan jawaban sekarang?')) kumpulkan();
}

// Timer berdasarkan jam server (koreksi selisih jam perangkat siswa)
const batasMs = new Date(props.batas).getTime();
const selisihJam = Date.now() - props.sekarangMs;
const teksTimer = computed(() => {
    if (sisa.value === null) return '--:--';
    const s = sisa.value, j = Math.floor(s / 3600), m = Math.floor((s % 3600) / 60), d = s % 60;
    return (j ? j + ':' : '') + String(m).padStart(2, '0') + ':' + String(d).padStart(2, '0');
});
function detak() {
    sisa.value = Math.max(0, Math.floor((batasMs - (Date.now() - selisihJam)) / 1000));
    if (sisa.value === 0) { status.value = 'Waktu habis, mengumpulkan...'; kumpulkan(); return; }
    timerDetak = setTimeout(detak, 1000);
}

onMounted(() => {
    detak();
    timerUlang = setInterval(() => Object.keys(antre).forEach((id) => kirim(Number(id), antre[id])), 8000);
});
onBeforeUnmount(() => {
    clearTimeout(timerDetak);
    clearInterval(timerUlang);
    Object.values(timerKetik).forEach(clearTimeout);
});
</script>

<template>
    <Head :title="kuis.judul" />
    <AppLayout>
        <div class="kepala">
            <div><div class="remah">{{ ruang.mapel }} · {{ ruang.kelas }}</div><h1><span class="sorot">{{ kuis.judul }}</span></h1></div>
            <div style="text-align:right"><div class="kecil-redup">Sisa waktu</div><div :class="['timer', sisa !== null && sisa <= 60 ? 'kritis' : '']">{{ teksTimer }}</div><div class="simpan-status">{{ status }}</div></div>
        </div>
        <div class="grid g-utama">
            <div class="kartu">
                <div v-for="(s, i) in soal" :key="s.id" :class="['soal-kartu', i === aktif ? 'aktif' : '']">
                    <div class="remah">Soal {{ i + 1 }} dari {{ soal.length }} · {{ s.jenis === 'pg' ? 'Pilihan ganda' : 'Esai' }}</div>
                    <div class="soal-tanya">{{ s.pertanyaan }}</div>
                    <template v-if="s.jenis === 'pg'">
                        <label v-for="(o, j) in s.opsi" :key="o.huruf" class="opsi"><input type="radio" :name="'s' + s.id" :value="o.huruf" :checked="jawaban[s.id] === o.huruf" @change="pilih(s, o.huruf)">
                            <span class="huruf">{{ String.fromCharCode(65 + j) }}</span><span>{{ o.teks }}</span></label>
                    </template>
                    <textarea v-else v-model="jawaban[s.id]" :name="'s' + s.id" class="besar" placeholder="Tulis jawabanmu..." @input="ketik(s)" />
                    <div style="display:flex;justify-content:space-between;margin-top:16px;gap:8px">
                        <button type="button" class="btn garis" :disabled="i === 0" @click="ke(i - 1)"><Icon name="back" :size="16" />Sebelumnya</button>
                        <button v-if="i < soal.length - 1" type="button" class="btn" @click="ke(i + 1)">Berikutnya<Icon name="arrow" :size="16" /></button>
                    </div>
                </div>
            </div>
            <div class="kartu">
                <h2><Icon name="grid" :size="18" />Nomor soal</h2>
                <div class="soal-nav">
                    <button v-for="(s, i) in soal" :key="s.id" type="button" :class="[i === aktif ? 'aktif' : '', terjawab(s) ? 'terjawab' : '']" @click="ke(i)">{{ i + 1 }}</button>
                </div>
                <p class="kecil-redup mt">{{ jumlahTerjawab }} dari {{ soal.length }} soal terjawab.</p>
                <button type="button" class="btn stabilo blok" @click="konfirmasiKumpul"><Icon name="check" :size="16" />Kumpulkan jawaban</button>
            </div>
        </div>
    </AppLayout>
</template>
