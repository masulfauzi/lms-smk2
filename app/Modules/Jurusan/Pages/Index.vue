<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Icon from '@/Components/Icon.vue';
import Kosong from '@/Components/Kosong.vue';
import TombolHapus from '@/Components/TombolHapus.vue';
import { formUbah } from '@/lib/formUbah';

const props = defineProps({ daftar: Array, ubah: Object });
const form = formUbah(() => props.ubah, (u) => ({ id: u?.id ?? '', kode: u?.kode ?? '', nama: u?.nama ?? '' }));
const simpan = () => form.post(route('admin.jurusan'), { preserveScroll: true });
</script>

<template>
    <Head title="Jurusan" />
    <AppLayout>
        <div class="kepala"><div><h1><span class="sorot">Jurusan</span></h1><p>Program keahlian (SMK) atau peminatan (SMA).</p></div></div>
        <div class="grid g-utama">
            <div class="kartu rapat"><div class="tabel-wrap"><table>
                <thead><tr><th>Kode</th><th>Nama jurusan</th><th class="angka">Kelas</th><th /></tr></thead>
                <tbody>
                    <tr v-for="j in daftar" :key="j.id"><td class="tebal">{{ j.kode }}</td><td>{{ j.nama }}</td><td class="angka">{{ j.kelas_count }}</td>
                        <td class="angka" style="white-space:nowrap"><Link class="ikon-btn" :href="route('admin.jurusan', { ubah: j.id })"><Icon name="edit" :size="16" /></Link><TombolHapus :url="route('admin.jurusan.hapus', j.id)" /></td></tr>
                    <tr v-if="!daftar.length"><td colspan="4"><Kosong ikon="flag" judul="Belum ada jurusan" /></td></tr>
                </tbody></table></div></div>
            <form class="kartu" @submit.prevent="simpan">
                <h2><Icon :name="ubah ? 'edit' : 'plus'" :size="18" />{{ ubah ? 'Ubah jurusan' : 'Tambah jurusan' }}</h2>
                <div class="field"><label>Kode</label><input v-model="form.kode" type="text" name="kode" placeholder="TKJ" required maxlength="15"></div>
                <div class="field"><label>Nama</label><input v-model="form.nama" type="text" name="nama" placeholder="Teknik Komputer dan Jaringan" required></div>
                <button type="submit" class="btn" :disabled="form.processing"><Icon name="check" :size="16" />Simpan</button> <Link v-if="ubah" class="btn garis" :href="route('admin.jurusan')">Batal</Link>
            </form>
        </div>
    </AppLayout>
</template>
