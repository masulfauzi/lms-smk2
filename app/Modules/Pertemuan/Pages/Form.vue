<script setup>
import { computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Icon from '@/Components/Icon.vue';
import TombolHapus from '@/Components/TombolHapus.vue';
import RuangKepala from '@modules/Ruang/Components/RuangKepala.vue';

const props = defineProps({ ruang: Object, pertemuan: Object });
const ada = computed(() => !!props.pertemuan.id);

const { id, lampiran_nama, ...awal } = props.pertemuan;
const form = useForm({ ...awal, lampiran: null, hapus_lampiran: false });

function simpan() {
    const opsi = { forceFormData: true, preserveScroll: true };
    if (ada.value) form.transform((d) => ({ ...d, _method: 'put' })).post(route('pertemuan.update', [props.ruang.id, id]), opsi);
    else form.post(route('pertemuan.store', props.ruang.id), opsi);
}
</script>

<template>
    <Head :title="(ada ? 'Ubah' : 'Tambah') + ' pertemuan'" />
    <AppLayout>
        <RuangKepala :ruang="ruang" />
        <form class="grid g-utama" @submit.prevent="simpan">
            <div class="kartu">
                <h2><Icon name="book" :size="18" />{{ ada ? 'Ubah pertemuan ' + pertemuan.urutan : 'Pertemuan baru' }}</h2>
                <div class="baris-form">
                    <div class="field" style="max-width:120px"><label>Pertemuan ke</label><input v-model="form.urutan" type="number" name="urutan" min="1" required></div>
                    <div class="field"><label>Tanggal</label><input v-model="form.tanggal" type="date" name="tanggal" required></div>
                </div>
                <div class="field"><label>Judul / topik</label><input v-model="form.judul" type="text" name="judul" required maxlength="160" placeholder="Contoh: Sistem Persamaan Linear Dua Variabel"></div>
                <div class="field"><label>Isi materi</label>
                    <textarea v-model="form.isi" name="isi" class="besar" placeholder="Tulis ringkasan materi, langkah belajar, atau petunjuk. Pisahkan paragraf dengan baris kosong. **teks** untuk huruf tebal." />
                    <small>Link (https://...) otomatis bisa diklik siswa.</small></div>
                <div class="field"><label>Link video YouTube (opsional)</label><input v-model="form.video_url" type="url" name="video_url" placeholder="https://www.youtube.com/watch?v=..."></div>
                <div class="field"><label>Lampiran (PDF, Word, PowerPoint, Excel, gambar, ZIP · maks. 20 MB)</label>
                    <input type="file" name="lampiran" @input="form.lampiran = $event.target.files[0]">
                    <small v-if="lampiran_nama">Saat ini: {{ lampiran_nama }} — <label class="cek" style="display:inline-flex;font-size:12px"><input v-model="form.hapus_lampiran" type="checkbox" name="hapus_lampiran"> hapus</label></small>
                </div>
            </div>
            <div>
                <div class="kartu">
                    <h2><Icon name="check-square" :size="18" />Presensi daring</h2>
                    <div class="field"><label class="cek"><input v-model="form.presensi" type="checkbox" name="presensi"> Buka presensi pada tanggal pertemuan</label></div>
                    <div v-show="form.presensi" class="baris-form">
                        <div class="field"><label>Jam buka</label><input v-model="form.jam_buka" type="time" name="jam_buka"></div>
                        <div class="field"><label>Jam tutup</label><input v-model="form.jam_tutup" type="time" name="jam_tutup"></div>
                    </div>
                    <small class="kecil-redup">Siswa yang tidak menekan "Saya hadir" sampai jam tutup tercatat alpa. Izin/sakit diisi guru.</small>
                </div>
                <div class="kartu">
                    <div class="field"><label class="cek"><input v-model="form.terbit" type="checkbox" name="terbit"> Terbitkan untuk siswa</label><small>Matikan untuk menyimpan sebagai draf.</small></div>
                    <div style="display:flex;gap:8px;flex-wrap:wrap">
                        <button type="submit" class="btn" :disabled="form.processing"><Icon name="check" :size="16" />Simpan</button>
                        <Link class="btn garis" :href="ada ? route('pertemuan.show', [ruang.id, id]) : route('ruang.show', ruang.id)">Batal</Link>
                    </div>
                </div>
            </div>
        </form>
        <div v-if="ada" class="mt"><TombolHapus :url="route('pertemuan.destroy', [ruang.id, id])" pesan="Hapus pertemuan ini beserta presensinya?" kelas="btn bahaya kecil" label="Hapus pertemuan" /></div>
    </AppLayout>
</template>
