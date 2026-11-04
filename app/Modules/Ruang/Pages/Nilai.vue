<script setup>
import { computed } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Icon from '@/Components/Icon.vue';
import RuangKepala from '../Components/RuangKepala.vue';

const props = defineProps({ ruang: Object, bobot: Object, tugas: Array, kuis: Array, baris: Array });
const siswa = computed(() => usePage().props.auth.user.role === 'siswa');
const b = computed(() => props.baris[0] ?? null);
const rendah = (n) => (n !== null && n < props.bobot.kkm ? 'n-rendah' : '');
</script>

<template>
    <Head :title="'Nilai · ' + ruang.judul" />
    <AppLayout>
        <RuangKepala :ruang="ruang" />
        <template v-if="siswa">
            <template v-if="b">
                <div class="grid g4">
                    <div class="stat tinta"><small><Icon name="award" :size="16" />Nilai akhir sementara</small><b>{{ b.akhir ?? '-' }}</b><div class="sub">Predikat {{ b.predikat }} · KKM {{ bobot.kkm }}</div></div>
                    <div class="stat"><small><Icon name="clipboard" :size="16" />Rata-rata tugas</small><b>{{ b.rata_tugas ?? '-' }}</b><div class="sub">bobot {{ bobot.bobot_tugas }}%</div></div>
                    <div class="stat"><small><Icon name="quiz" :size="16" />Rata-rata kuis</small><b>{{ b.rata_kuis ?? '-' }}</b><div class="sub">bobot {{ bobot.bobot_kuis }}%</div></div>
                    <div class="stat"><small><Icon name="check-square" :size="16" />Kehadiran</small><b>{{ b.persen_hadir !== null ? b.persen_hadir + '%' : '-' }}</b><div class="sub">{{ b.hadir }} hadir · {{ b.alpa }} alpa</div></div>
                </div>
                <div class="grid g2 mt">
                    <div class="kartu rapat"><h2><Icon name="clipboard" :size="18" />Nilai tugas</h2>
                        <div class="tabel-wrap mt"><table><thead><tr><th>Tugas</th><th>Tenggat</th><th class="angka">Nilai</th></tr></thead><tbody>
                            <tr v-for="(t, i) in tugas" :key="t.id"><td><Link :href="route('tugas.show', [ruang.id, t.id])">{{ t.judul }}</Link></td><td class="kecil-redup">{{ t.tenggat }}</td>
                                <td :class="['angka tebal', rendah(b.tugas[i])]">{{ b.tugas[i] ?? 'Belum' }}</td></tr>
                            <tr v-if="!tugas.length"><td colspan="3" class="kecil-redup">Belum ada tugas.</td></tr>
                        </tbody></table></div></div>
                    <div class="kartu rapat"><h2><Icon name="quiz" :size="18" />Nilai kuis</h2>
                        <div class="tabel-wrap mt"><table><thead><tr><th>Kuis</th><th>Tanggal</th><th class="angka">Nilai</th></tr></thead><tbody>
                            <tr v-for="(k, i) in kuis" :key="k.id"><td>{{ k.judul }}</td><td class="kecil-redup">{{ k.tanggal }}</td>
                                <td :class="['angka tebal', rendah(b.kuis[i])]">{{ b.kuis[i] === null ? 'Belum' : (k.tampilkan_nilai ? b.kuis[i] : 'Disembunyikan') }}</td></tr>
                            <tr v-if="!kuis.length"><td colspan="3" class="kecil-redup">Belum ada kuis.</td></tr>
                        </tbody></table></div></div>
                </div>
            </template>
            <p class="kecil-redup mt">Nilai akhir dihitung dari tugas yang sudah dinilai, kuis yang sudah selesai, dan presensi yang sudah ditutup. Tugas yang tidak dikumpulkan sampai tenggat bernilai 0.</p>
        </template>
        <div v-else class="kartu rapat">
            <h2><Icon name="award" :size="18" />Rekap nilai {{ ruang.kelas }}
                <span class="kanan"><a class="btn kecil garis" :href="route('ruang.nilai.csv', ruang.id)"><Icon name="download" :size="14" />Unduh CSV</a></span></h2>
            <p class="kecil-redup" style="padding:4px 20px 0">Bobot: tugas {{ bobot.bobot_tugas }}%, kuis {{ bobot.bobot_kuis }}%, kehadiran {{ bobot.bobot_kehadiran }}% · KKM {{ bobot.kkm }}. Ubah di tab Pengaturan.</p>
            <div class="tabel-wrap mt"><table class="matriks">
                <thead><tr><th class="no">No</th><th>Nama</th>
                    <th v-for="(t, i) in tugas" :key="'t' + t.id" :title="t.judul"><Link :href="route('tugas.show', [ruang.id, t.id])">T{{ i + 1 }}</Link></th>
                    <th v-for="(k, i) in kuis" :key="'k' + k.id" :title="k.judul"><Link :href="route('kuis.show', [ruang.id, k.id])">K{{ i + 1 }}</Link></th>
                    <th>Rata T</th><th>Rata K</th><th>% Hadir</th><th>Akhir</th><th>Pred.</th></tr></thead>
                <tbody>
                    <tr v-for="(s, i) in baris" :key="s.id"><td class="no">{{ i + 1 }}</td><td class="tebal" style="white-space:nowrap">{{ s.nama }}</td>
                        <td v-for="(n, j) in s.tugas" :key="'t' + j" :class="rendah(n)">{{ n ?? '·' }}</td>
                        <td v-for="(n, j) in s.kuis" :key="'k' + j" :class="rendah(n)">{{ n === null ? '·' : Math.round(n) }}</td>
                        <td>{{ s.rata_tugas ?? '-' }}</td><td>{{ s.rata_kuis ?? '-' }}</td><td>{{ s.persen_hadir ?? '-' }}</td>
                        <td :class="['tebal', rendah(s.akhir)]">{{ s.akhir ?? '-' }}</td><td>{{ s.predikat }}</td></tr>
                    <tr v-if="!baris.length"><td colspan="99" class="kecil-redup">Belum ada siswa di kelas ini.</td></tr>
                </tbody>
            </table></div>
            <p class="kecil-redup" style="padding:12px 20px">Tanda · = belum tenggat / belum dinilai (tidak ikut dihitung). Merah = di bawah KKM.</p>
        </div>
    </AppLayout>
</template>
