<script setup>
import { computed } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Icon from '@/Components/Icon.vue';
import Kosong from '@/Components/Kosong.vue';
import TombolHapus from '@/Components/TombolHapus.vue';
import { formUbah } from '@/lib/formUbah';

const props = defineProps({ daftar: Array, jurusan: Array, guru: Array, tingkat: Array, ubah: Object });
const jenjang = computed(() => usePage().props.sekolah.jenjang);
const pakaiJurusan = computed(() => ['SMA', 'SMK'].includes(jenjang.value));
const contohNama = computed(() => (jenjang.value === 'SD' ? '4A' : (jenjang.value === 'SMP' ? 'VIII B' : 'X TKJ 1')));

const form = formUbah(() => props.ubah, (u) => ({
    id: u?.id ?? '', tingkat: u?.tingkat ?? props.tingkat[0], jurusan_id: u?.jurusan_id ?? '', nama: u?.nama ?? '', wali_id: u?.wali_id ?? '',
}));
const simpan = () => form.post(route('admin.kelas'), { preserveScroll: true });
</script>

<template>
    <Head title="Kelas" />
    <AppLayout>
        <div class="kepala"><div><h1><span class="sorot">Kelas</span></h1><p>Rombongan belajar jenjang {{ jenjang }} beserta wali kelasnya.</p></div></div>
        <div class="grid g-utama">
            <div class="kartu rapat"><div class="tabel-wrap"><table>
                <thead><tr><th>Kelas</th><th>Tingkat</th><th v-if="pakaiJurusan">Jurusan</th><th>Wali kelas</th><th class="angka">Siswa</th><th class="angka">Mapel</th><th /></tr></thead>
                <tbody>
                    <tr v-for="k in daftar" :key="k.id"><td class="tebal"><Link :href="route('admin.pengguna', { role: 'siswa', kelas: k.id })">{{ k.nama }}</Link></td><td>{{ k.tingkat }}</td>
                        <td v-if="pakaiJurusan">{{ k.jurusan ?? '-' }}</td>
                        <td>{{ k.wali ?? '-' }}</td><td class="angka">{{ k.siswa_count }}</td><td class="angka"><Link :href="route('admin.ruang', { kelas: k.id })">{{ k.ruang_count }}</Link></td>
                        <td class="angka" style="white-space:nowrap"><Link class="ikon-btn" :href="route('admin.kelas', { ubah: k.id })"><Icon name="edit" :size="16" /></Link><TombolHapus :url="route('admin.kelas.hapus', k.id)" /></td></tr>
                    <tr v-if="!daftar.length"><td colspan="7"><Kosong ikon="grid" judul="Belum ada kelas" /></td></tr>
                </tbody></table></div></div>
            <form class="kartu" @submit.prevent="simpan">
                <h2><Icon :name="ubah ? 'edit' : 'plus'" :size="18" />{{ ubah ? 'Ubah kelas' : 'Tambah kelas' }}</h2>
                <div class="baris-form">
                    <div class="field"><label>Tingkat</label><select v-model="form.tingkat" name="tingkat"><option v-for="t in tingkat" :key="t" :value="t">{{ t }}</option></select></div>
                    <div v-if="pakaiJurusan" class="field"><label>Jurusan</label><select v-model="form.jurusan_id" name="jurusan_id"><option value="">-</option><option v-for="j in jurusan" :key="j.id" :value="j.id">{{ j.kode }}</option></select></div>
                </div>
                <div class="field"><label>Nama kelas</label><input v-model="form.nama" type="text" name="nama" :placeholder="contohNama" required maxlength="40"></div>
                <div class="field"><label>Wali kelas</label><select v-model="form.wali_id" name="wali_id"><option value="">-</option><option v-for="g in guru" :key="g.id" :value="g.id">{{ g.name }}</option></select></div>
                <button type="submit" class="btn" :disabled="form.processing"><Icon name="check" :size="16" />Simpan</button> <Link v-if="ubah" class="btn garis" :href="route('admin.kelas')">Batal</Link>
            </form>
        </div>
    </AppLayout>
</template>
