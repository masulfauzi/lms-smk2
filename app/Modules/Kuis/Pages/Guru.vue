<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Icon from '@/Components/Icon.vue';
import TombolHapus from '@/Components/TombolHapus.vue';
import RuangKepala from '@modules/Ruang/Components/RuangKepala.vue';

defineProps({ ruang: Object, kuis: Object, stat: Object, hasil: Array, butir: Array });
</script>

<template>
    <Head :title="'Hasil · ' + kuis.judul" />
    <AppLayout>
        <RuangKepala :ruang="ruang" />
        <div class="kepala">
            <div><div class="remah">Kuis · {{ kuis.periode }}</div><h1><span class="sorot">{{ kuis.judul }}</span></h1></div>
            <div class="aksi"><Link class="btn garis" :href="route('kuis.edit', [ruang.id, kuis.id])"><Icon name="edit" :size="16" />Soal &amp; pengaturan</Link></div>
        </div>
        <div class="grid g4">
            <div class="stat tinta"><small>Status</small><b style="font-size:20px">{{ stat.status }}</b></div>
            <div class="stat"><small>Mengumpulkan</small><b>{{ stat.mengumpulkan }}</b><div class="sub">{{ stat.mengerjakan }} sedang mengerjakan</div></div>
            <div class="stat"><small>Rata-rata</small><b>{{ stat.rata }}</b><div class="sub">tertinggi {{ stat.tertinggi }} · terendah {{ stat.terendah }}</div></div>
            <div class="stat"><small>Tuntas (KKM {{ ruang.kkm }})</small><b>{{ stat.tuntas }}</b><div class="sub">{{ stat.menunggu }} menunggu koreksi esai</div></div>
        </div>
        <div class="grid g-utama mt">
            <div class="kartu rapat">
                <h2><Icon name="users" :size="18" />Hasil per siswa</h2>
                <div class="tabel-wrap mt"><table>
                    <thead><tr><th class="no">No</th><th>Nama</th><th>Status</th><th>Waktu</th><th class="angka">Nilai</th><th /></tr></thead>
                    <tbody>
                        <tr v-for="(s, i) in hasil" :key="s.id"><td class="no">{{ i + 1 }}</td><td class="tebal">{{ s.nama }}</td>
                            <td><span :class="['lencana', s.status[0]]">{{ s.status[1] }}</span></td>
                            <td class="kecil-redup">{{ s.waktu }}</td>
                            <td :class="['angka tebal', s.rendah ? 'n-rendah' : '']">{{ s.nilai ?? '-' }}</td>
                            <td style="white-space:nowrap"><Link v-if="s.selesai" class="btn kecil garis" :href="route('kuis.koreksi', [ruang.id, kuis.id, s.percobaan_id])">{{ s.perlu_koreksi ? 'Koreksi' : 'Lihat' }}</Link>
                                <TombolHapus v-if="s.percobaan_id" :url="route('kuis.reset', [ruang.id, kuis.id, s.percobaan_id])" :pesan="`Hapus jawaban ${s.nama} agar bisa mengerjakan ulang?`" /></td></tr>
                    </tbody>
                </table></div>
            </div>
            <div class="kartu">
                <h2><Icon name="chart" :size="18" />Analisis butir soal</h2>
                <p class="kecil-redup">Persentase siswa yang menjawab benar. Di bawah 40% = soal sulit / perlu dibahas ulang.</p>
                <div v-for="b in butir" :key="b.id" style="margin-bottom:12px" :title="b.pertanyaan">
                    <div style="display:flex;justify-content:space-between;font-size:13px"><span class="potong" style="max-width:75%">{{ b.judul }}</span>
                        <b :class="b.persen !== null && b.persen < 40 ? 'n-rendah' : ''">{{ b.persen ?? '-' }}{{ b.persen !== null ? '%' : '' }}</b></div>
                    <div class="butir"><i :style="{ width: (b.persen ?? 0) + '%', background: (b.persen ?? 0) < 40 ? 'var(--margin)' : 'var(--hijau)' }" /></div>
                    <div v-if="b.sebaran.length" class="kecil-redup" style="margin-top:2px">{{ b.sebaran.join(' · ') }}</div>
                </div>
                <p v-if="!butir.length" class="kecil-redup">Tidak ada soal pilihan ganda.</p>
            </div>
        </div>
    </AppLayout>
</template>
