<script setup>
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import PilihKelas from '../Components/PilihKelas.vue';

defineProps({ kelasList: Array, kelas: Object, stat: Object, siswa: Array });
const lencana = { belum: ['merah', 'Belum pernah'], lama: ['oranye', 'Perlu dihubungi'], hari_ini: ['hijau', 'Hari ini'], aktif: ['biru', 'Aktif'] };
</script>

<template>
    <Head title="Keaktifan siswa" />
    <AppLayout>
        <div class="kepala">
            <div><h1><span class="sorot">Keaktifan siswa</span></h1><p>Kapan terakhir siswa kelas {{ kelas?.nama }} membuka LMS. Gunakan untuk menindaklanjuti siswa yang tidak mengikuti PJJ.</p></div>
            <div class="aksi"><PilihKelas :kelas-list="kelasList" :kelas="kelas" rute="admin.rekap.keaktifan" /></div>
        </div>
        <div class="grid g4">
            <div class="stat tinta"><small>Siswa</small><b>{{ stat.siswa }}</b></div>
            <div class="stat"><small>Aktif hari ini</small><b>{{ stat.hari_ini }}</b></div>
            <div class="stat"><small>Aktif 3 hari terakhir</small><b>{{ stat.aktif }}</b></div>
            <div class="stat"><small>Perlu dihubungi</small><b style="color:var(--margin)">{{ stat.lama }}</b><div class="sub">tidak aktif lebih dari 3 hari</div></div>
        </div>
        <div class="kartu rapat mt"><div class="tabel-wrap"><table>
            <thead><tr><th class="no">No</th><th>Nama</th><th>NISN</th><th>Terakhir aktif</th><th>Status</th></tr></thead>
            <tbody>
                <tr v-for="(s, i) in siswa" :key="s.id"><td class="no">{{ i + 1 }}</td><td class="tebal">{{ s.nama }}</td><td>{{ s.nisn }}</td>
                    <td class="kecil-redup">{{ s.terakhir }}</td>
                    <td><span :class="['lencana', lencana[s.status][0]]">{{ lencana[s.status][1] }}</span></td></tr>
            </tbody>
        </table></div></div>
    </AppLayout>
</template>
