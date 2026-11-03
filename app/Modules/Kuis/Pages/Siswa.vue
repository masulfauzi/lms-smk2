<script setup>
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Icon from '@/Components/Icon.vue';
import Kosong from '@/Components/Kosong.vue';
import TombolAksi from '@/Components/TombolAksi.vue';
import RuangKepala from '@modules/Ruang/Components/RuangKepala.vue';

defineProps({ ruang: Object, kuis: Object, percobaan: Object });
</script>

<template>
    <Head :title="kuis.judul" />
    <AppLayout>
        <RuangKepala :ruang="ruang" />
        <div class="grid g2">
            <div class="kartu">
                <div class="remah">Kuis · {{ kuis.jumlah_soal }} soal · {{ kuis.durasi_menit }} menit</div>
                <h1 style="font-size:23px;margin-bottom:12px"><span class="sorot">{{ kuis.judul }}</span></h1>
                <div v-if="kuis.petunjuk_html" class="materi" style="font-size:14.5px" v-html="kuis.petunjuk_html" />
                <table class="mt">
                    <tr><td class="kecil-redup">Dibuka</td><td>{{ kuis.mulai }}</td></tr>
                    <tr><td class="kecil-redup">Ditutup</td><td>{{ kuis.selesai }}</td></tr>
                    <tr><td class="kecil-redup">Durasi</td><td>{{ kuis.durasi_menit }} menit sejak tombol Mulai ditekan</td></tr>
                </table>
            </div>
            <div class="kartu">
                <template v-if="percobaan && percobaan.selesai">
                    <div class="nilai-besar">
                        <template v-if="percobaan.nilai !== null"><span>Nilai kamu</span><b>{{ percobaan.nilai }}</b><span>{{ percobaan.tuntas ? 'Tuntas' : 'Belum tuntas' }} · KKM {{ ruang.kkm }}</span></template>
                        <template v-else-if="percobaan.menunggu"><span>Jawaban terkumpul</span><b style="font-size:30px">Menunggu koreksi</b><span>Soal esai sedang dikoreksi guru.</span></template>
                        <template v-else><span>Jawaban terkumpul</span><b style="font-size:30px">Selesai</b><span>Nilai akan diumumkan guru.</span></template>
                    </div>
                    <p class="kecil-redup" style="text-align:center">{{ percobaan.keterangan }}</p>
                </template>
                <template v-else-if="kuis.status === 'berlangsung'">
                    <h2><Icon name="quiz" :size="18" />Siap mengerjakan?</h2>
                    <ul style="font-size:14px;color:var(--redup);padding-left:18px">
                        <li>Waktu {{ kuis.durasi_menit }} menit mulai berjalan setelah tombol ditekan dan tidak bisa dihentikan.</li>
                        <li>Jawaban tersimpan otomatis setiap kamu memilih/mengetik.</li>
                        <li>Saat waktu habis, jawaban terkumpul otomatis.</li>
                    </ul>
                    <TombolAksi :href="route('kuis.mulai', [ruang.id, kuis.id])" kelas="btn stabilo blok" :konfirmasi="percobaan ? '' : 'Mulai mengerjakan sekarang? Waktu langsung berjalan.'">
                        <Icon name="arrow" :size="16" />{{ percobaan ? 'Lanjutkan mengerjakan' : 'Mulai mengerjakan' }}</TombolAksi>
                </template>
                <Kosong v-else-if="kuis.status === 'belum'" ikon="clock" judul="Kuis belum dibuka" :teks="'Dibuka ' + kuis.mulai" />
                <Kosong v-else ikon="x" judul="Kuis sudah ditutup" teks="Kamu tidak mengerjakan kuis ini." />
            </div>
        </div>
    </AppLayout>
</template>
