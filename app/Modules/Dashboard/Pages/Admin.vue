<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Icon from '@/Components/Icon.vue';
import Kosong from '@/Components/Kosong.vue';
import DaftarPengumuman from '../Components/DaftarPengumuman.vue';

defineProps({ nama: String, stat: Object, perKelas: Array, guruBelumIsi: Array, pengumuman: Array });
</script>

<template>
    <Head title="Beranda" />
    <AppLayout>
        <div class="kepala">
            <div><h1>Halo, <span class="sorot">{{ nama }}</span></h1><p>Ringkasan pembelajaran jarak jauh {{ $page.props.sekolah.nama }} hari ini.</p></div>
            <div class="aksi"><Link class="btn" :href="route('admin.ruang')"><Icon name="clipboard" :size="16" />Pembagian mengajar</Link></div>
        </div>
        <div class="grid g4">
            <div class="stat tinta"><small><Icon name="users" :size="16" />Siswa aktif</small><b>{{ stat.siswa }}</b><div class="sub">{{ stat.aktif_hari_ini }} membuka LMS hari ini</div></div>
            <div class="stat"><small><Icon name="user" :size="16" />Guru</small><b>{{ stat.guru }}</b></div>
            <div class="stat"><small><Icon name="grid" :size="16" />Kelas</small><b>{{ stat.kelas }}</b></div>
            <div class="stat"><small><Icon name="book" :size="16" />Ruang belajar</small><b>{{ stat.ruang }}</b><div class="sub">semester aktif</div></div>
        </div>
        <div class="grid g-utama mt">
            <div>
                <div class="kartu">
                    <h2><Icon name="check-square" :size="18" />Kehadiran PJJ hari ini <Link class="kanan" :href="route('admin.rekap.presensi')">Rekap bulanan</Link></h2>
                    <div v-for="k in perKelas" :key="k.kelas" class="batang">
                        <span class="nama">{{ k.kelas }}</span>
                        <span class="trek"><i :style="{ width: k.persen + '%', background: k.persen < 60 ? 'var(--margin)' : 'var(--tinta)' }" /></span>
                        <b>{{ k.persen }}%</b>
                    </div>
                    <Kosong v-if="!perKelas.length" ikon="calendar" judul="Belum ada pertemuan hari ini" teks="Persentase muncul setelah guru membuat pertemuan bertanggal hari ini." />
                    <p v-else class="kecil-redup">Persen = siswa yang presensi hadir dibanding seluruh siswa x jumlah pertemuan hari ini di kelas tersebut.</p>
                </div>
                <div class="kartu">
                    <h2><Icon name="alert" :size="18" />Ruang belajar tanpa materi 7 hari terakhir</h2>
                    <p v-if="!guruBelumIsi.length" class="kecil-redup">Semua ruang belajar aktif mengunggah materi minggu ini.</p>
                    <ul v-else class="daftar">
                        <li v-for="r in guruBelumIsi" :key="r.id"><span class="ikon merah"><Icon name="book" :size="18" /></span>
                            <span class="teks"><b>{{ r.judul }}</b><small>{{ r.guru }}</small></span>
                            <Link class="btn kecil garis" :href="route('ruang.show', r.id)">Lihat</Link></li>
                    </ul>
                </div>
            </div>
            <div><DaftarPengumuman :pengumuman="pengumuman" /></div>
        </div>
    </AppLayout>
</template>
