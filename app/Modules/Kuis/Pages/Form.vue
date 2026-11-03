<script setup>
import { computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Icon from '@/Components/Icon.vue';
import Kosong from '@/Components/Kosong.vue';
import TombolHapus from '@/Components/TombolHapus.vue';
import RuangKepala from '@modules/Ruang/Components/RuangKepala.vue';
import { formUbah } from '@/lib/formUbah';

const props = defineProps({ ruang: Object, kuis: Object, soal: Array, totalBobot: Number, sudahDikerjakan: Boolean, ubahSoal: Object });
// id diambil dari props (bukan disalin sekali) karena setelah kuis dibuat, halaman yang sama dimuat ulang dengan id baru
const id = computed(() => props.kuis.id);
const ada = computed(() => !!id.value);
const HURUF = ['A', 'B', 'C', 'D', 'E'];

// Pengaturan kuis
const { id: _id, ...awal } = props.kuis;
const form = useForm(awal);
function simpan() {
    if (ada.value) form.put(route('kuis.update', [props.ruang.id, id.value]), { preserveScroll: true });
    else form.post(route('kuis.store', props.ruang.id), { preserveState: 'errors' });
}

// Tambah / ubah soal (?soal=ID)
const isiSoal = (s) => ({
    soal_id: s?.id ?? '', jenis: s?.jenis ?? 'pg', bobot: s?.bobot ?? 1, pertanyaan: s?.pertanyaan ?? '',
    kunci: s?.kunci ?? '', opsi: Object.fromEntries(HURUF.map((h) => [h, s?.opsi?.[h] ?? ''])),
});
const formSoal = formUbah(() => props.ubahSoal, isiSoal);
const simpanSoal = () => formSoal.post(route('kuis.soal', [props.ruang.id, id.value]), {
    preserveScroll: true,
    onSuccess: () => { formSoal.defaults(isiSoal(props.ubahSoal)); formSoal.reset(); },
});

// Impor soal dari teks
const formImpor = useForm({ teks: '' });
const impor = () => formImpor.post(route('kuis.impor', [props.ruang.id, id.value]), { preserveScroll: true, onSuccess: () => formImpor.reset() });

const contohImpor = '1. Ibu kota provinsi Jawa Tengah adalah ...\nA. Surabaya\nB. Semarang\nC. Bandung\nD. Yogyakarta\nKunci: B\n\n2. Jelaskan pengertian fotosintesis!';
</script>

<template>
    <Head :title="(ada ? 'Ubah' : 'Buat') + ' kuis'" />
    <AppLayout>
        <RuangKepala :ruang="ruang" />
        <div class="grid g-utama">
            <div>
                <template v-if="ada">
                    <div class="kartu rapat">
                        <h2><Icon name="quiz" :size="18" />Soal <span class="kanan kecil-redup">{{ soal.length }} soal · total bobot {{ totalBobot }}</span></h2>
                        <p v-if="sudahDikerjakan" class="flash error" style="margin:12px 20px 0">Sudah ada siswa yang mengerjakan. Mengubah kunci/bobot akan menghitung ulang nilai mereka.</p>
                        <div style="padding:6px 20px 16px">
                            <div v-for="s in soal" :key="s.id" style="padding:14px 0;border-bottom:1px dashed var(--garis-2)">
                                <div style="display:flex;gap:10px;align-items:flex-start">
                                    <b style="color:var(--tinta)">{{ s.urutan }}.</b>
                                    <div style="flex:1;min-width:0">
                                        <div style="white-space:pre-line">{{ s.pertanyaan }}</div>
                                        <div v-if="s.jenis === 'pg'" style="display:grid;gap:4px;margin-top:8px;font-size:14px">
                                            <div v-for="(o, h) in s.opsi" :key="h" :style="h === s.kunci ? 'color:var(--hijau);font-weight:700' : 'color:var(--redup)'">{{ h }}. {{ o }} <Icon v-if="h === s.kunci" name="check" :size="14" /></div>
                                        </div>
                                        <div style="margin-top:6px"><span :class="['lencana', s.jenis === 'pg' ? 'biru' : 'kuning']">{{ s.jenis === 'pg' ? 'Pilihan ganda' : 'Esai' }}</span> <span class="lencana">Bobot {{ s.bobot }}</span></div>
                                    </div>
                                    <Link class="ikon-btn" :href="route('kuis.edit', { ruang: ruang.id, kuis: id, soal: s.id }) + '#form-soal'" title="Ubah"><Icon name="edit" :size="16" /></Link>
                                    <TombolHapus :url="route('kuis.soal.hapus', [ruang.id, id, s.id])" :pesan="`Hapus soal nomor ${s.urutan}?`" />
                                </div>
                            </div>
                            <Kosong v-if="!soal.length" ikon="quiz" judul="Belum ada soal" teks="Tambah satu per satu atau impor sekaligus dari teks." />
                        </div>
                    </div>

                    <form id="form-soal" class="kartu" @submit.prevent="simpanSoal">
                        <h2><Icon :name="ubahSoal ? 'edit' : 'plus'" :size="18" />{{ ubahSoal ? 'Ubah soal nomor ' + ubahSoal.urutan : 'Tambah soal' }}</h2>
                        <div class="baris-form">
                            <div class="field"><label>Jenis</label><select v-model="formSoal.jenis" name="jenis"><option value="pg">Pilihan ganda (dinilai otomatis)</option><option value="esai">Esai (dikoreksi guru)</option></select></div>
                            <div class="field" style="max-width:120px"><label>Bobot</label><input v-model="formSoal.bobot" type="number" name="bobot" min="1" max="100" required></div>
                        </div>
                        <div class="field"><label>Pertanyaan</label><textarea v-model="formSoal.pertanyaan" name="pertanyaan" required style="min-height:90px" /></div>
                        <div v-show="formSoal.jenis === 'pg'">
                            <div v-for="h in HURUF" :key="h" class="inline-form" style="margin-bottom:8px">
                                <label class="cek" style="width:58px"><input v-model="formSoal.kunci" type="radio" name="kunci" :value="h"> {{ h }}</label>
                                <div class="field" style="flex:1"><input v-model="formSoal.opsi[h]" type="text" :name="`opsi[${h}]`" :placeholder="`Opsi ${h}${h === 'E' ? ' (opsional)' : ''}`"></div>
                            </div>
                            <small class="kecil-redup">Pilih bulatan di kiri sebagai kunci jawaban.</small>
                        </div>
                        <div style="display:flex;gap:8px;margin-top:12px">
                            <button type="submit" class="btn" :disabled="formSoal.processing"><Icon name="check" :size="16" />{{ ubahSoal ? 'Simpan perubahan' : 'Tambah soal' }}</button>
                            <Link v-if="ubahSoal" class="btn garis" :href="route('kuis.edit', [ruang.id, id])">Batal</Link>
                        </div>
                    </form>

                    <form class="kartu" @submit.prevent="impor">
                        <h2><Icon name="upload" :size="18" />Impor soal dari teks</h2>
                        <p class="kecil-redup">Tempel soal dari Word/Google Docs dengan format di bawah. Soal tanpa opsi dianggap esai.</p>
                        <textarea v-model="formImpor.teks" name="teks" class="besar" :placeholder="contohImpor" />
                        <button type="submit" class="btn garis mt" :disabled="formImpor.processing"><Icon name="upload" :size="16" />Impor soal</button>
                    </form>
                </template>
            </div>

            <form class="kartu" @submit.prevent="simpan">
                <h2><Icon name="settings" :size="18" />Pengaturan kuis</h2>
                <div class="field"><label>Judul</label><input v-model="form.judul" type="text" name="judul" required maxlength="160" placeholder="Contoh: Ulangan Harian Bab 2"></div>
                <div class="field"><label>Petunjuk</label><textarea v-model="form.petunjuk" name="petunjuk" style="min-height:80px" placeholder="Kerjakan dengan jujur. Waktu berjalan sejak tombol Mulai ditekan." /></div>
                <div class="field"><label>Dibuka</label><input v-model="form.mulai" type="datetime-local" name="mulai" required></div>
                <div class="field"><label>Ditutup</label><input v-model="form.selesai" type="datetime-local" name="selesai" required></div>
                <div class="field"><label>Durasi mengerjakan (menit)</label><input v-model="form.durasi_menit" type="number" name="durasi_menit" min="5" max="600" required><small>Waktu tetap berhenti saat kuis ditutup.</small></div>
                <div class="field"><label class="cek"><input v-model="form.acak_soal" type="checkbox" name="acak_soal"> Acak urutan soal</label></div>
                <div class="field"><label class="cek"><input v-model="form.acak_opsi" type="checkbox" name="acak_opsi"> Acak urutan opsi jawaban</label></div>
                <div class="field"><label class="cek"><input v-model="form.tampilkan_nilai" type="checkbox" name="tampilkan_nilai"> Tampilkan nilai ke siswa setelah selesai</label></div>
                <div v-if="ada" class="field catatan" style="border-left-color:var(--stabilo)"><label class="cek"><input v-model="form.terbit" type="checkbox" name="terbit"> Terbitkan untuk siswa</label></div>
                <div style="display:flex;gap:8px;flex-wrap:wrap">
                    <button type="submit" class="btn" :disabled="form.processing"><Icon name="check" :size="16" />{{ ada ? 'Simpan' : 'Buat kuis & isi soal' }}</button>
                    <Link v-if="ada" class="btn garis" :href="route('kuis.show', [ruang.id, id])">Lihat hasil</Link>
                </div>
            </form>
        </div>
        <div v-if="ada" class="mt"><TombolHapus :url="route('kuis.destroy', [ruang.id, id])" pesan="Hapus kuis ini beserta semua jawaban siswa?" kelas="btn bahaya kecil" label="Hapus kuis" /></div>
    </AppLayout>
</template>
