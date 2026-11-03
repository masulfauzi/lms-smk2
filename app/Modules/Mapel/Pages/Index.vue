<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Icon from '@/Components/Icon.vue';
import Kosong from '@/Components/Kosong.vue';
import TombolHapus from '@/Components/TombolHapus.vue';
import { formUbah } from '@/lib/formUbah';

const props = defineProps({ daftar: Array, ubah: Object });
const form = formUbah(() => props.ubah, (u) => ({ id: u?.id ?? '', kode: u?.kode ?? '', nama: u?.nama ?? '', kelompok: u?.kelompok ?? 'Umum' }));
const simpan = () => form.post(route('admin.mapel'), { preserveScroll: true });
const warna = (k) => (k === 'Kejuruan' ? 'kuning' : (k === 'Umum' ? 'biru' : ''));
</script>

<template>
    <Head title="Mata pelajaran" />
    <AppLayout>
        <div class="kepala"><div><h1><span class="sorot">Mata pelajaran</span></h1><p>Daftar mapel yang bisa dibagikan ke kelas.</p></div></div>
        <div class="grid g-utama">
            <div class="kartu rapat"><div class="tabel-wrap"><table>
                <thead><tr><th>Kode</th><th>Mata pelajaran</th><th>Kelompok</th><th class="angka">Dipakai</th><th /></tr></thead>
                <tbody>
                    <tr v-for="m in daftar" :key="m.id"><td class="tebal">{{ m.kode }}</td><td>{{ m.nama }}</td><td><span :class="['lencana', warna(m.kelompok)]">{{ m.kelompok }}</span></td>
                        <td class="angka">{{ m.dipakai }} kelas</td>
                        <td class="angka" style="white-space:nowrap"><Link class="ikon-btn" :href="route('admin.mapel', { ubah: m.id })"><Icon name="edit" :size="16" /></Link><TombolHapus :url="route('admin.mapel.hapus', m.id)" /></td></tr>
                    <tr v-if="!daftar.length"><td colspan="5"><Kosong ikon="layers" judul="Belum ada mata pelajaran" /></td></tr>
                </tbody></table></div></div>
            <form class="kartu" @submit.prevent="simpan">
                <h2><Icon :name="ubah ? 'edit' : 'plus'" :size="18" />{{ ubah ? 'Ubah mapel' : 'Tambah mapel' }}</h2>
                <div class="field"><label>Kode</label><input v-model="form.kode" type="text" name="kode" placeholder="MTK" required maxlength="15"></div>
                <div class="field"><label>Nama</label><input v-model="form.nama" type="text" name="nama" placeholder="Matematika" required></div>
                <div class="field"><label>Kelompok</label><select v-model="form.kelompok" name="kelompok"><option v-for="k in ['Umum', 'Kejuruan', 'Muatan Lokal']" :key="k">{{ k }}</option></select></div>
                <button type="submit" class="btn" :disabled="form.processing"><Icon name="check" :size="16" />Simpan</button> <Link v-if="ubah" class="btn garis" :href="route('admin.mapel')">Batal</Link>
            </form>
        </div>
    </AppLayout>
</template>
