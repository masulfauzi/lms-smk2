<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { formUbah } from '@/lib/formUbah';
import AppLayout from '@/Layouts/AppLayout.vue';
import Icon from '@/Components/Icon.vue';
import Kosong from '@/Components/Kosong.vue';
import TombolHapus from '@/Components/TombolHapus.vue';
import TambahJadwal from '../Components/TambahJadwal.vue';

const props = defineProps({ kelasList: Array, kelas: Object, tahunAjaran: String, hari: Object, ruang: Array, mapelTersedia: Array, guru: Array });

const pilihKelas = (e) => router.get(route('admin.ruang'), { kelas: e.target.value });
const gantiGuru = (r, e) => router.post(route('admin.ruang'), { kelas_id: props.kelas.id, mapel_id: r.mapel_id, guru_id: e.target.value }, { preserveScroll: true });

// Daftar mapel tersedia berubah setelah menambah, jadi form diisi ulang dari props tiap simpan berhasil
const form = formUbah(() => null, () => ({ kelas_id: props.kelas?.id, mapel_id: props.mapelTersedia[0]?.id ?? '', guru_id: '' }));
const tambah = () => form.post(route('admin.ruang'), { preserveScroll: true });
</script>

<template>
    <Head title="Pembagian mengajar" />
    <AppLayout>
        <div class="kepala">
            <div><h1><span class="sorot">Pembagian mengajar</span></h1><p>Mapel, guru pengampu, dan jadwal kelas daring · {{ tahunAjaran ?? 'tahun ajaran belum aktif' }}</p></div>
            <form class="inline-form" @submit.prevent><div class="field"><select name="kelas" :value="kelas?.id" @change="pilihKelas">
                <option v-for="k in kelasList" :key="k.id" :value="k.id">{{ k.nama }}</option>
            </select></div></form>
        </div>
        <div v-if="!tahunAjaran" class="flash error">Belum ada tahun ajaran aktif. Atur di menu Pengaturan.</div>
        <div v-else-if="!kelas" class="kartu"><Kosong ikon="grid" judul="Belum ada kelas" teks="Tambahkan kelas terlebih dahulu." /></div>
        <div v-else class="grid g-utama">
            <div class="kartu rapat">
                <h2><Icon name="clipboard" :size="18" />Kelas {{ kelas.nama }} <span class="kanan kecil-redup">{{ ruang.length }} mapel</span></h2>
                <div class="tabel-wrap mt"><table>
                    <thead><tr><th>Mapel</th><th>Guru</th><th>Jadwal</th><th /></tr></thead>
                    <tbody>
                        <tr v-for="r in ruang" :key="r.id">
                            <td class="tebal"><Link :href="route('ruang.show', r.id)">{{ r.mapel }}</Link></td>
                            <td><form class="inline-form" @submit.prevent>
                                <div class="field"><select name="guru_id" :value="r.guru_id ?? ''" style="min-width:170px" @change="gantiGuru(r, $event)"><option value="">Belum ada guru</option><option v-for="g in guru" :key="g.id" :value="g.id">{{ g.name }}</option></select></div></form></td>
                            <td style="font-size:13px">
                                <div v-for="j in r.jadwal" :key="j.id" style="display:flex;gap:4px;align-items:center">{{ j.waktu }}
                                    <Icon v-if="j.link_meet" name="video" :size="13" /> <TombolHapus :url="route('admin.jadwal.hapus', j.id)" pesan="Hapus jadwal ini?" /></div>
                                <TambahJadwal :ruang-id="r.id" :hari="hari" />
                            </td>
                            <td class="angka"><TombolHapus :url="route('admin.ruang.hapus', r.id)" :pesan="`Hapus ${r.mapel} dari kelas ${kelas.nama}?`" /></td>
                        </tr>
                        <tr v-if="!ruang.length"><td colspan="4"><Kosong ikon="clipboard" judul="Belum ada mapel di kelas ini" /></td></tr>
                    </tbody>
                </table></div>
            </div>
            <form class="kartu" @submit.prevent="tambah">
                <h2><Icon name="plus" :size="18" />Tambah mapel ke {{ kelas.nama }}</h2>
                <div class="field"><label>Mata pelajaran</label><select v-model="form.mapel_id" name="mapel_id" required>
                    <option v-for="m in mapelTersedia" :key="m.id" :value="m.id">{{ m.nama }}</option></select></div>
                <div class="field"><label>Guru pengampu</label><select v-model="form.guru_id" name="guru_id"><option value="">Belum ditentukan</option><option v-for="g in guru" :key="g.id" :value="g.id">{{ g.name }}</option></select></div>
                <button type="submit" class="btn" :disabled="form.processing"><Icon name="plus" :size="16" />Tambah</button>
                <p class="kecil-redup mt">Setiap mapel di satu kelas menjadi satu "ruang belajar" tempat guru mengunggah materi, tugas, dan kuis.</p>
            </form>
        </div>
    </AppLayout>
</template>
