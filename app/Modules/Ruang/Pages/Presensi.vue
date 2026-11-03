<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Icon from '@/Components/Icon.vue';
import Kosong from '@/Components/Kosong.vue';
import RuangKepala from '../Components/RuangKepala.vue';

defineProps({ ruang: Object, status: Object, pertemuan: Array, baris: Array });
</script>

<template>
    <Head :title="'Presensi · ' + ruang.judul" />
    <AppLayout>
        <RuangKepala :ruang="ruang" />
        <div class="kartu rapat">
            <h2><Icon name="check-square" :size="18" />Rekap presensi <span class="kanan" style="display:flex;gap:6px">
                <span class="sel h">H</span><span class="kecil-redup">hadir</span><span class="sel i">I</span><span class="kecil-redup">izin</span><span class="sel s">S</span><span class="kecil-redup">sakit</span><span class="sel a">A</span><span class="kecil-redup">alpa</span></span></h2>
            <Kosong v-if="!pertemuan.length" ikon="check-square" judul="Belum ada pertemuan dengan presensi" />
            <template v-else>
                <div class="tabel-wrap mt"><table class="matriks">
                    <thead><tr><th>No</th><th>Nama</th>
                        <th v-for="p in pertemuan" :key="p.id" :title="p.judul"><Link :href="route('pertemuan.show', [ruang.id, p.id])">P{{ p.urutan }}</Link><div class="kecil-redup" style="font-weight:400;text-transform:none">{{ p.tanggal }}</div></th>
                        <th>H</th><th>I/S</th><th>A</th><th>%</th></tr></thead>
                    <tbody>
                        <tr v-for="(s, i) in baris" :key="s.id"><td>{{ i + 1 }}</td><td class="tebal" style="white-space:nowrap">{{ s.nama }}</td>
                            <td v-for="(st, j) in s.sel" :key="j"><span v-if="st" :class="['sel', st[0]]" :title="status[st]">{{ st[0].toUpperCase() }}</span><span v-else class="sel k">·</span></td>
                            <td class="tebal">{{ s.h }}</td><td>{{ s.is }}</td><td :class="s.a ? 'n-rendah' : ''">{{ s.a }}</td><td>{{ s.persen ?? '-' }}</td></tr>
                    </tbody>
                </table></div>
                <p class="kecil-redup" style="padding:12px 20px">Siswa yang tidak presensi sampai jam tutup otomatis tercatat alpa. Guru bisa mengubah status (izin/sakit) di halaman tiap pertemuan.</p>
            </template>
        </div>
    </AppLayout>
</template>
