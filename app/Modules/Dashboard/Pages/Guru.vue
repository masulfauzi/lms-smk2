<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Icon from '@/Components/Icon.vue';
import Kosong from '@/Components/Kosong.vue';
import JadwalHariIni from '../Components/JadwalHariIni.vue';
import DaftarPengumuman from '../Components/DaftarPengumuman.vue';

defineProps({ nama: String, tanggal: String, ruang: Array, perluDinilai: Array, jumlahPerluDinilai: Number, kuisAktif: Array, jadwal: Object, pengumuman: Array });
</script>

<template>
    <Head title="Beranda" />
    <AppLayout>
        <div class="kepala">
            <div><h1>Selamat mengajar, <span class="sorot">{{ nama }}</span></h1><p>{{ tanggal }}</p></div>
        </div>
        <div class="grid g4">
            <div class="stat tinta"><small><Icon name="book" :size="16" />Ruang belajar</small><b>{{ ruang.length }}</b><div class="sub">semester ini</div></div>
            <div class="stat"><small><Icon name="clipboard" :size="16" />Perlu dinilai</small><b>{{ jumlahPerluDinilai }}</b><div class="sub">pengumpulan tugas</div></div>
            <div class="stat"><small><Icon name="quiz" :size="16" />Kuis aktif</small><b>{{ kuisAktif.length }}</b></div>
            <div class="stat"><small><Icon name="calendar" :size="16" />Jadwal hari ini</small><b>{{ jadwal.daftar.length }}</b></div>
        </div>
        <div class="grid g-utama mt">
            <div>
                <div class="kartu">
                    <h2><Icon name="clipboard" :size="18" />Tugas menunggu penilaian</h2>
                    <ul v-if="perluDinilai.length" class="daftar">
                        <li v-for="p in perluDinilai" :key="p.id"><span class="ikon kuning"><Icon name="file" :size="18" /></span>
                            <span class="teks"><b class="potong">{{ p.judul }}</b>
                                <small>{{ p.keterangan }}<template v-if="p.terlambat"> · <span style="color:var(--merah)">terlambat</span></template></small></span>
                            <Link class="btn kecil" :href="p.url">Nilai</Link></li>
                    </ul>
                    <Kosong v-else ikon="check" judul="Semua tugas sudah dinilai" />
                </div>
                <div class="kartu">
                    <h2><Icon name="book" :size="18" />Ruang belajar saya</h2>
                    <div class="grid g2">
                        <Link v-for="r in ruang" :key="r.id" class="mapel-kartu" :href="route('ruang.show', r.id)">
                            <div class="warna" :style="{ background: r.warna }" />
                            <div class="isi"><h3>{{ r.mapel }}</h3><p>{{ r.keterangan }}</p></div>
                        </Link>
                    </div>
                    <Kosong v-if="!ruang.length" judul="Belum ada ruang belajar" teks="Admin sekolah perlu mengatur pembagian mengajar." />
                </div>
            </div>
            <div>
                <JadwalHariIni :jadwal="jadwal" />
                <div v-if="kuisAktif.length" class="kartu">
                    <h2><Icon name="quiz" :size="18" />Kuis aktif</h2>
                    <ul class="daftar">
                        <li v-for="k in kuisAktif" :key="k.id"><span class="teks"><b>{{ k.judul }}</b><small>{{ k.keterangan }}</small></span>
                            <Link class="btn kecil garis" :href="k.url">Hasil</Link></li>
                    </ul>
                </div>
                <DaftarPengumuman :pengumuman="pengumuman" />
            </div>
        </div>
    </AppLayout>
</template>
