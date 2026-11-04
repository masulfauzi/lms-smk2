<script setup>
import { computed } from 'vue';
import { Head, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Icon from '@/Components/Icon.vue';
import RuangKepala from '../Components/RuangKepala.vue';

defineProps({ ruang: Object, siswa: Array, pengajar: Object });
const bukanSiswa = computed(() => usePage().props.auth.user.role !== 'siswa');
</script>

<template>
    <Head :title="'Anggota · ' + ruang.judul" />
    <AppLayout>
        <RuangKepala :ruang="ruang" />
        <div class="grid g-utama">
            <div class="kartu rapat">
                <h2><Icon name="users" :size="18" />Siswa kelas {{ ruang.kelas }} <span class="kanan kecil-redup">{{ siswa.length }} siswa</span></h2>
                <div class="tabel-wrap mt"><table>
                    <thead><tr><th class="no">No</th><th>Nama</th><th>NISN</th><th>L/P</th><th v-if="bukanSiswa">Terakhir aktif</th></tr></thead>
                    <tbody>
                        <tr v-for="(s, i) in siswa" :key="s.id"><td class="no">{{ i + 1 }}</td><td class="tebal">{{ s.name }}</td><td>{{ s.username }}</td><td>{{ s.jk ?? '-' }}</td>
                            <td v-if="bukanSiswa" class="kecil-redup">{{ s.terakhir_aktif }}</td></tr>
                    </tbody>
                </table></div>
            </div>
            <div class="kartu">
                <h2><Icon name="user" :size="18" />Pengajar</h2>
                <ul class="daftar">
                    <li><span class="avatar guru">{{ pengajar.guru.inisial }}</span><span class="teks"><b>{{ pengajar.guru.nama }}</b><small>Guru {{ ruang.mapel }}</small></span></li>
                    <li v-if="pengajar.wali"><span class="avatar">{{ pengajar.wali.inisial }}</span><span class="teks"><b>{{ pengajar.wali.nama }}</b><small>Wali kelas {{ ruang.kelas }}</small></span></li>
                </ul>
            </div>
        </div>
    </AppLayout>
</template>
