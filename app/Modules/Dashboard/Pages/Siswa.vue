<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Icon from '@/Components/Icon.vue';
import Kosong from '@/Components/Kosong.vue';
import TombolAksi from '@/Components/TombolAksi.vue';
import JadwalHariIni from '../Components/JadwalHariIni.vue';
import DaftarPengumuman from '../Components/DaftarPengumuman.vue';

defineProps({ nama: String, tanggal: String, kelas: String, presensiTerbuka: Array, tugas: Array, kuis: Array, ruang: Array, jadwal: Object, pengumuman: Array });
</script>

<template>
    <Head title="Beranda" />
    <AppLayout>
        <div class="kepala">
            <div><h1>Hai, <span class="sorot">{{ nama }}</span></h1><p>{{ tanggal }} · Kelas {{ kelas }}</p></div>
        </div>

        <div v-for="p in presensiTerbuka" :key="p.id" class="presensi-panel buka" style="margin-bottom:12px">
            <span class="daftar"><span class="ikon hijau" style="display:grid"><Icon name="check-square" :size="20" /></span></span>
            <div class="teks"><b>Presensi {{ p.mapel }} sedang dibuka</b><div class="kecil-redup">{{ p.keterangan }}</div></div>
            <TombolAksi :href="p.url_hadir" kelas="btn hijau"><Icon name="check" :size="16" />Saya hadir</TombolAksi>
        </div>

        <div class="grid g-utama">
            <div>
                <div class="kartu">
                    <h2><Icon name="clipboard" :size="18" />Tugas yang harus dikumpulkan</h2>
                    <ul v-if="tugas.length" class="daftar">
                        <li v-for="t in tugas" :key="t.id"><span :class="['ikon', t.lewat ? 'merah' : 'kuning']"><Icon name="clipboard" :size="18" /></span>
                            <span class="teks"><b class="potong">{{ t.judul }}</b><small>{{ t.keterangan }}</small></span>
                            <span :class="['lencana', t.lencana]">{{ t.label }}</span>
                            <Link class="btn kecil" :href="t.url">Kerjakan</Link></li>
                    </ul>
                    <Kosong v-else ikon="check" judul="Tidak ada tugas tertunda" teks="Semua tugas sudah kamu kumpulkan." />
                </div>
                <div v-if="kuis.length" class="kartu">
                    <h2><Icon name="quiz" :size="18" />Kuis &amp; ulangan</h2>
                    <ul class="daftar">
                        <li v-for="k in kuis" :key="k.id"><span class="ikon"><Icon name="quiz" :size="18" /></span>
                            <span class="teks"><b>{{ k.judul }}</b><small>{{ k.keterangan }}</small></span>
                            <Link v-if="k.berlangsung" class="btn kecil stabilo" :href="k.url">Kerjakan</Link><span v-else class="lencana">Belum dibuka</span></li>
                    </ul>
                </div>
                <div class="kartu">
                    <h2><Icon name="book" :size="18" />Mata pelajaran</h2>
                    <div class="grid g2">
                        <Link v-for="r in ruang" :key="r.id" class="mapel-kartu" :href="route('ruang.show', r.id)">
                            <div class="warna" :style="{ background: r.warna }" />
                            <div class="isi">
                                <h3>{{ r.mapel }}</h3><p>{{ r.guru }}</p>
                                <div class="progres"><i :style="{ width: r.progres + '%' }" /></div>
                                <div class="progres-label"><span>Materi dipelajari</span><b>{{ r.progres }}%</b></div>
                            </div>
                        </Link>
                    </div>
                </div>
            </div>
            <div>
                <JadwalHariIni :jadwal="jadwal" />
                <DaftarPengumuman :pengumuman="pengumuman" />
            </div>
        </div>
    </AppLayout>
</template>
