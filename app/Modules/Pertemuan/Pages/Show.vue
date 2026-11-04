<script setup>
import { computed } from 'vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Icon from '@/Components/Icon.vue';
import TombolAksi from '@/Components/TombolAksi.vue';
import RuangKepala from '@modules/Ruang/Components/RuangKepala.vue';

const props = defineProps({
    ruang: Object, pertemuan: Object, tugas: Array, sebelum: Object, sesudah: Object,
    panelSiswa: Object, selesai: Boolean, statusLabel: Object, hadirCount: Number, presensiSiswa: Array,
});
const siswa = computed(() => usePage().props.auth.user.role === 'siswa');
const huruf = { hadir: 'H', izin: 'I', sakit: 'S', alpa: 'A' };

// Form presensi guru: status & keterangan per siswa
const form = useForm({
    status: Object.fromEntries(props.presensiSiswa.map((s) => [s.id, s.status])),
    ket: Object.fromEntries(props.presensiSiswa.map((s) => [s.id, s.ket])),
});
const simpanPresensi = () => form
    .transform((d) => ({ ...d, status: Object.fromEntries(Object.entries(d.status).filter(([, v]) => v)) }))
    .post(route('pertemuan.presensi', [props.ruang.id, props.pertemuan.id]), { preserveScroll: true });
</script>

<template>
    <Head :title="pertemuan.judul" />
    <AppLayout>
        <RuangKepala :ruang="ruang" />
        <div class="grid g-utama">
            <div>
                <div class="kartu">
                    <div class="remah">Pertemuan {{ pertemuan.urutan }} · {{ pertemuan.tanggal }} <span v-if="!pertemuan.terbit" class="lencana">Draf</span></div>
                    <h1 style="font-size:24px;margin-bottom:16px"><span class="sorot">{{ pertemuan.judul }}</span></h1>
                    <div v-if="pertemuan.youtube_id" class="video"><iframe :src="`https://www.youtube-nocookie.com/embed/${pertemuan.youtube_id}`" title="Video materi" allow="accelerometer; encrypted-media; gyroscope; picture-in-picture" allowfullscreen loading="lazy" /></div>
                    <p v-else-if="pertemuan.video_url"><a :href="pertemuan.video_url" target="_blank" rel="noopener"><Icon name="video" :size="16" /> Tonton video materi</a></p>
                    <div v-if="pertemuan.isi_html" class="materi" v-html="pertemuan.isi_html" />
                    <div v-else class="materi"><p class="kecil-redup">Tidak ada teks materi.</p></div>
                    <a v-if="pertemuan.lampiran_nama" class="lampiran mt" :href="route('berkas.pertemuan', [ruang.id, pertemuan.id])" target="_blank">
                        <span class="ikon"><Icon name="file" :size="20" /></span>
                        <span style="flex:1;min-width:0"><b class="potong" style="display:block">{{ pertemuan.lampiran_nama }}</b><small class="kecil-redup">Lampiran materi · klik untuk membuka</small></span>
                        <Icon name="download" :size="18" />
                    </a>
                    <Link v-for="t in tugas" :key="t.id" class="lampiran mt" :href="route('tugas.show', [ruang.id, t.id])" style="border-color:var(--stabilo)">
                        <span class="ikon" style="background:var(--stabilo-lembut);color:#8a6500"><Icon name="clipboard" :size="20" /></span>
                        <span style="flex:1;min-width:0"><b class="potong" style="display:block">Tugas: {{ t.judul }}</b><small class="kecil-redup">Tenggat {{ t.tenggat }}</small></span>
                        <Icon name="arrow" :size="18" />
                    </Link>
                    <div style="display:flex;justify-content:space-between;gap:10px;margin-top:24px;flex-wrap:wrap">
                        <Link v-if="sebelum" class="btn garis kecil" :href="route('pertemuan.show', [ruang.id, sebelum.id])"><Icon name="back" :size="14" />Pertemuan {{ sebelum.urutan }}</Link><span v-else />
                        <Link v-if="sesudah" class="btn garis kecil" :href="route('pertemuan.show', [ruang.id, sesudah.id])">Pertemuan {{ sesudah.urutan }}<Icon name="arrow" :size="14" /></Link>
                    </div>
                </div>
            </div>

            <div>
                <template v-if="siswa">
                    <template v-if="panelSiswa">
                        <div v-if="panelSiswa.jenis === 'sudah'" class="presensi-panel sudah"><div class="teks"><b>{{ panelSiswa.judul }}</b><div class="kecil-redup">{{ panelSiswa.teks }}</div></div></div>
                        <div v-else-if="panelSiswa.jenis === 'buka'" class="presensi-panel buka"><div class="teks"><b>{{ panelSiswa.judul }}</b><div class="kecil-redup">{{ panelSiswa.teks }}</div></div>
                            <TombolAksi :href="route('pertemuan.hadir', [ruang.id, pertemuan.id])" kelas="btn hijau"><Icon name="check" :size="16" />Saya hadir</TombolAksi></div>
                        <div v-else-if="panelSiswa.jenis === 'belum'" class="presensi-panel"><div class="teks"><b>{{ panelSiswa.judul }}</b><div class="kecil-redup">{{ panelSiswa.teks }}</div></div></div>
                        <div v-else class="presensi-panel" style="border-color:#f0bdb5"><div class="teks"><b style="color:var(--merah)">{{ panelSiswa.judul }}</b><div class="kecil-redup">{{ panelSiswa.teks }}</div></div></div>
                    </template>
                    <div class="kartu mt">
                        <h2><Icon name="check" :size="18" />Progres belajar</h2>
                        <p class="kecil-redup">Tandai setelah kamu selesai membaca materi dan menonton videonya.</p>
                        <TombolAksi :href="route('pertemuan.selesai', [ruang.id, pertemuan.id])" :kelas="'btn blok ' + (selesai ? 'garis' : 'stabilo')">
                            <Icon :name="selesai ? 'x' : 'check'" :size="16" />{{ selesai ? 'Batalkan tanda selesai' : 'Tandai sudah dipelajari' }}</TombolAksi>
                    </div>
                </template>
                <div v-else class="kartu">
                    <h2><Icon name="settings" :size="18" />Kelola</h2>
                    <div style="display:flex;gap:8px;flex-wrap:wrap">
                        <Link class="btn garis kecil" :href="route('pertemuan.edit', [ruang.id, pertemuan.id])"><Icon name="edit" :size="14" />Ubah</Link>
                        <Link class="btn garis kecil" :href="route('tugas.create', { ruang: ruang.id, pertemuan: pertemuan.id })"><Icon name="clipboard" :size="14" />Tambah tugas</Link>
                    </div>
                    <p v-if="pertemuan.ada_presensi" class="kecil-redup mt">Presensi {{ pertemuan.presensi_label }}
                        <span v-if="pertemuan.presensi_terbuka" class="lencana hijau">Sedang dibuka</span></p>
                </div>
            </div>
        </div>

        <form v-if="!siswa && pertemuan.ada_presensi" class="kartu rapat mt" @submit.prevent="simpanPresensi">
            <h2><Icon name="check-square" :size="18" />Presensi pertemuan {{ pertemuan.urutan }}
                <span class="kanan kecil-redup">{{ hadirCount }} hadir dari {{ presensiSiswa.length }} siswa</span></h2>
            <div class="tabel-wrap mt"><table>
                <thead><tr><th class="no">No</th><th>Nama</th><th>Waktu presensi</th><th>Status</th><th>Keterangan</th></tr></thead>
                <tbody>
                    <tr v-for="(s, i) in presensiSiswa" :key="s.id"><td class="no">{{ i + 1 }}</td><td class="tebal">{{ s.nama }}</td>
                        <td class="kecil-redup">{{ s.waktu }}</td>
                        <td><div class="status-pilih">
                            <label v-for="(hrf, val) in huruf" :key="val" :title="statusLabel[val]"><input v-model="form.status[s.id]" type="radio" :name="`status[${s.id}]`" :value="val"><span :class="hrf.toLowerCase()">{{ hrf }}</span></label>
                        </div></td>
                        <td><input v-model="form.ket[s.id]" type="text" :name="`ket[${s.id}]`" maxlength="255" placeholder="opsional" style="min-width:140px"></td></tr>
                </tbody>
            </table></div>
            <div style="padding:14px 20px"><button type="submit" class="btn" :disabled="form.processing"><Icon name="check" :size="16" />Simpan presensi</button></div>
        </form>
    </AppLayout>
</template>
