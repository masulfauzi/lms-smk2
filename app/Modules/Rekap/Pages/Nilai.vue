<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Icon from '@/Components/Icon.vue';
import Kosong from '@/Components/Kosong.vue';
import PilihKelas from '../Components/PilihKelas.vue';

defineProps({ kelasList: Array, kelas: Object, tahunAjaran: String, ruang: Array, siswa: Array });
const cetak = () => window.print();
</script>

<template>
    <Head title="Leger nilai" />
    <AppLayout>
        <div class="kepala">
            <div><h1><span class="sorot">Leger nilai</span></h1><p>Nilai akhir sementara semua mapel kelas {{ kelas?.nama }} · {{ tahunAjaran }}</p></div>
            <div class="aksi">
                <PilihKelas :kelas-list="kelasList" :kelas="kelas" rute="admin.rekap.nilai" />
                <a v-if="kelas" class="btn garis" :href="route('admin.rekap.nilai', { kelas: kelas.id, csv: 1 })"><Icon name="download" :size="16" />CSV</a>
                <button type="button" class="btn garis" @click="cetak"><Icon name="printer" :size="16" />Cetak</button>
            </div>
        </div>
        <div class="kartu rapat"><div class="tabel-wrap"><table class="matriks">
            <thead><tr><th>No</th><th>Nama</th><th v-for="r in ruang" :key="r.id" :title="r.mapel"><Link :href="route('ruang.nilai', r.id)">{{ r.kode }}</Link></th><th>Rata-rata</th><th>Rank</th></tr></thead>
            <tbody>
                <tr v-for="(s, i) in siswa" :key="s.id"><td>{{ i + 1 }}</td><td class="tebal" style="white-space:nowrap">{{ s.nama }}</td>
                    <td v-for="r in ruang" :key="r.id" :class="s.nilai[r.id] !== null && s.nilai[r.id] < r.kkm ? 'n-rendah' : ''">{{ s.nilai[r.id] ?? '·' }}</td>
                    <td class="tebal">{{ s.rata ?? '-' }}</td><td>{{ s.rank ?? '-' }}</td></tr>
                <tr v-if="!siswa.length"><td colspan="99"><Kosong ikon="award" judul="Tidak ada data" /></td></tr>
            </tbody>
        </table></div></div>
        <p class="kecil-redup mt">Kolom memakai kode mapel. Merah = di bawah KKM mapel. Nilai bersifat sementara dan berubah seiring tugas/kuis baru.</p>
    </AppLayout>
</template>
