<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Icon from '@/Components/Icon.vue';
import TombolAksi from '@/Components/TombolAksi.vue';

const props = defineProps({ profil: Object, tahun: Array });

const formProfil = useForm({ ...props.profil });
const simpanProfil = () => formProfil.post(route('admin.sekolah'), { preserveScroll: true });

const formTahun = useForm({ nama: '', semester: 'Ganjil', salin: true });
const tambahTahun = () => formTahun.post(route('admin.tahun.store'), { preserveScroll: true, onSuccess: () => formTahun.reset('nama') });
</script>

<template>
    <Head title="Pengaturan sekolah" />
    <AppLayout>
        <div class="kepala"><div><h1><span class="sorot">Pengaturan sekolah</span></h1><p>Profil sekolah, jenjang, dan tahun ajaran.</p></div></div>
        <div class="grid g2">
            <form class="kartu" @submit.prevent="simpanProfil">
                <h2><Icon name="school" :size="18" />Profil sekolah</h2>
                <div class="field"><label>Nama sekolah</label><input v-model="formProfil.nama_sekolah" type="text" name="nama_sekolah" required></div>
                <div class="baris-form">
                    <div class="field"><label>Jenjang</label><select v-model="formProfil.jenjang" name="jenjang"><option v-for="j in ['SD', 'SMP', 'SMA', 'SMK']" :key="j">{{ j }}</option></select></div>
                    <div class="field"><label>NPSN</label><input v-model="formProfil.npsn" type="text" name="npsn"></div>
                    <div class="field"><label>KKM bawaan</label><input v-model="formProfil.kkm" type="number" name="kkm" min="0" max="100" required></div>
                </div>
                <div class="field"><label>Alamat</label><input v-model="formProfil.alamat" type="text" name="alamat"></div>
                <div class="field"><label>Kepala sekolah</label><input v-model="formProfil.kepala_sekolah" type="text" name="kepala_sekolah"></div>
                <p class="kecil-redup">Jenjang menentukan pilihan tingkat kelas (SD 1–6, SMP 7–9, SMA/SMK 10–12). Menu Jurusan muncul untuk SMA dan SMK.</p>
                <button type="submit" class="btn" :disabled="formProfil.processing"><Icon name="check" :size="16" />Simpan</button>
            </form>
            <div>
                <div class="kartu rapat">
                    <h2><Icon name="calendar" :size="18" />Tahun ajaran</h2>
                    <div class="tabel-wrap mt"><table>
                        <thead><tr><th>Tahun ajaran</th><th>Semester</th><th class="angka">Ruang</th><th /></tr></thead>
                        <tbody>
                            <tr v-for="t in tahun" :key="t.id"><td class="tebal">{{ t.nama }}</td><td>{{ t.semester }}</td><td class="angka">{{ t.ruang_count }}</td>
                                <td class="angka"><span v-if="t.aktif" class="lencana hijau">Aktif</span>
                                    <TombolAksi v-else :href="route('admin.tahun.aktif', t.id)" kelas="btn kecil garis" :konfirmasi="`Aktifkan ${t.label}? Guru & siswa akan melihat ruang belajar semester ini.`">Aktifkan</TombolAksi></td></tr>
                        </tbody>
                    </table></div>
                </div>
                <form class="kartu" @submit.prevent="tambahTahun">
                    <h2><Icon name="plus" :size="18" />Tambah semester</h2>
                    <div class="baris-form">
                        <div class="field"><label>Tahun ajaran</label><input v-model="formTahun.nama" type="text" name="nama" placeholder="2027/2028" pattern="\d{4}/\d{4}" required></div>
                        <div class="field"><label>Semester</label><select v-model="formTahun.semester" name="semester"><option>Ganjil</option><option>Genap</option></select></div>
                    </div>
                    <div class="field"><label class="cek"><input v-model="formTahun.salin" type="checkbox" name="salin"> Salin pembagian mengajar &amp; jadwal dari semester aktif</label></div>
                    <button type="submit" class="btn garis" :disabled="formTahun.processing"><Icon name="plus" :size="16" />Tambah</button>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
