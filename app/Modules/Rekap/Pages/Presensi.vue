<script setup>
import { reactive } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Icon from '@/Components/Icon.vue';
import Kosong from '@/Components/Kosong.vue';

const props = defineProps({ kelasList: Array, kelas: Object, bulan: String, bulanLabel: String, jumlahPertemuan: Number, rekap: Array });
const filter = reactive({ kelas: props.kelas?.id, bulan: props.bulan });
const tampilkan = () => router.get(route('admin.rekap.presensi'), filter);
const cetak = () => window.print();
</script>

<template>
    <Head title="Rekap presensi" />
    <AppLayout>
        <div class="kepala">
            <div><h1><span class="sorot">Rekap presensi</span></h1><p>Gabungan semua mapel kelas {{ kelas?.nama }} bulan {{ bulanLabel }} · {{ jumlahPertemuan }} pertemuan dengan presensi.</p></div>
            <div class="aksi">
                <form class="inline-form" @submit.prevent="tampilkan">
                    <div class="field"><select v-model="filter.kelas" name="kelas"><option v-for="k in kelasList" :key="k.id" :value="k.id">{{ k.nama }}</option></select></div>
                    <div class="field"><input v-model="filter.bulan" type="month" name="bulan"></div>
                    <button type="submit" class="btn garis">Tampilkan</button>
                </form>
                <button type="button" class="btn garis" @click="cetak"><Icon name="printer" :size="16" />Cetak</button>
            </div>
        </div>
        <div class="kartu rapat"><div class="tabel-wrap"><table>
            <thead><tr><th class="no">No</th><th>Nama</th><th>NISN</th><th class="angka">Hadir</th><th class="angka">Izin</th><th class="angka">Sakit</th><th class="angka">Alpa</th><th class="angka">% Hadir</th></tr></thead>
            <tbody>
                <tr v-for="(r, i) in rekap" :key="r.id"><td class="no">{{ i + 1 }}</td><td class="tebal">{{ r.nama }}</td><td>{{ r.nisn }}</td>
                    <td class="angka">{{ r.hadir }}</td><td class="angka">{{ r.izin }}</td><td class="angka">{{ r.sakit }}</td>
                    <td :class="['angka', r.alpa >= 3 ? 'n-rendah' : '']">{{ r.alpa }}</td><td :class="['angka tebal', r.persen !== null && r.persen < 75 ? 'n-rendah' : '']">{{ r.persen !== null ? r.persen + '%' : '-' }}</td></tr>
                <tr v-if="!rekap.length"><td colspan="8"><Kosong ikon="check-square" judul="Tidak ada data" /></td></tr>
            </tbody>
        </table></div></div>
        <p class="kecil-redup mt">Hanya menghitung pertemuan yang presensinya sudah ditutup. Merah: alpa 3 kali atau lebih / kehadiran di bawah 75%.</p>
    </AppLayout>
</template>
