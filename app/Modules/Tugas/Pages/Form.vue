<script setup>
import { computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Icon from '@/Components/Icon.vue';
import TombolHapus from '@/Components/TombolHapus.vue';
import RuangKepala from '@modules/Ruang/Components/RuangKepala.vue';

const props = defineProps({ ruang: Object, tugas: Object, jenis: Object, pertemuan: Array });
const ada = computed(() => !!props.tugas.id);

const { id, lampiran_nama, ...awal } = props.tugas;
const form = useForm({ ...awal, lampiran: null });

function simpan() {
    const opsi = { forceFormData: true, preserveScroll: true };
    if (ada.value) form.transform((d) => ({ ...d, _method: 'put' })).post(route('tugas.update', [props.ruang.id, id]), opsi);
    else form.post(route('tugas.store', props.ruang.id), opsi);
}
</script>

<template>
    <Head :title="(ada ? 'Ubah' : 'Buat') + ' tugas'" />
    <AppLayout>
        <RuangKepala :ruang="ruang" />
        <form class="grid g-utama" @submit.prevent="simpan">
            <div class="kartu">
                <h2><Icon name="clipboard" :size="18" />{{ ada ? 'Ubah tugas' : 'Tugas baru' }}</h2>
                <div class="field"><label>Judul tugas</label><input v-model="form.judul" type="text" name="judul" required maxlength="160" placeholder="Contoh: Latihan Soal SPLDV"></div>
                <div class="field"><label>Instruksi</label><textarea v-model="form.instruksi" name="instruksi" class="besar" placeholder="Jelaskan apa yang harus dikerjakan, format jawaban, dan kriteria penilaian." /></div>
                <div class="field"><label>Lampiran soal (opsional, maks. 20 MB)</label><input type="file" name="lampiran" @input="form.lampiran = $event.target.files[0]">
                    <small v-if="lampiran_nama">Saat ini: {{ lampiran_nama }}</small></div>
            </div>
            <div>
                <div class="kartu">
                    <div class="field"><label>Tenggat</label><input v-model="form.tenggat" type="datetime-local" name="tenggat" required></div>
                    <div class="field"><label>Bentuk pengumpulan</label><select v-model="form.jenis_kumpul" name="jenis_kumpul">
                        <option v-for="(l, v) in jenis" :key="v" :value="v">{{ l }}</option></select></div>
                    <div class="field"><label>Terkait pertemuan</label><select v-model="form.pertemuan_id" name="pertemuan_id"><option value="">Tidak terkait</option>
                        <option v-for="p in pertemuan" :key="p.id" :value="p.id">{{ p.label }}</option></select></div>
                    <div class="field"><label class="cek"><input v-model="form.boleh_terlambat" type="checkbox" name="boleh_terlambat"> Terima pengumpulan terlambat</label><small>Pengumpulan setelah tenggat diberi tanda "terlambat".</small></div>
                    <div style="display:flex;gap:8px;flex-wrap:wrap">
                        <button type="submit" class="btn" :disabled="form.processing"><Icon name="check" :size="16" />{{ ada ? 'Simpan' : 'Terbitkan tugas' }}</button>
                        <Link class="btn garis" :href="route('tugas.index', ruang.id)">Batal</Link>
                    </div>
                </div>
                <TombolHapus v-if="ada" :url="route('tugas.destroy', [ruang.id, id])" pesan="Hapus tugas ini beserta semua pengumpulan siswa?" kelas="btn bahaya kecil" label="Hapus tugas" />
            </div>
        </form>
    </AppLayout>
</template>
