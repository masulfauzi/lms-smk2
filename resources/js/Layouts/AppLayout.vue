<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import Icon from '@/Components/Icon.vue';
import Flash from '@/Components/Flash.vue';
import TombolAksi from '@/Components/TombolAksi.vue';

const page = usePage();
const u = computed(() => page.props.auth.user);
const sekolah = computed(() => page.props.sekolah);
const pakaiJurusan = computed(() => ['SMA', 'SMK'].includes(sekolah.value.jenjang));
const path = computed(() => page.url.split('?')[0]);

const on = (...nama) => (nama.some((n) => route().current(n)) ? 'on' : '');
const onPath = (awal) => (path.value.startsWith(awal) ? 'on' : '');
const bukaMenu = () => document.body.classList.toggle('menu-buka');
const tutupMenu = (e) => { if (e.target.closest('a')) document.body.classList.remove('menu-buka'); };
</script>

<template>
    <div class="atas-hp">
        <button type="button" aria-label="Buka menu" @click="bukaMenu"><Icon name="menu" :size="24" /></button>
        <b>{{ sekolah.nama }}</b>
    </div>
    <div class="app">
        <aside class="sisi" @click="tutupMenu">
            <div class="merek">
                <div class="logo"><Icon name="school" :size="22" /></div>
                <div><b>{{ sekolah.nama }}</b><small>LMS PJJ · {{ $page.props.tahun_ajaran ?? 'TA belum diatur' }}</small></div>
            </div>
            <Link :href="route('home')" :class="['nav', on('home')]"><Icon name="home" />Beranda</Link>
            <Link :href="route('ruang')" :class="['nav', on('ruang*', 'pertemuan.*', 'tugas.*', 'kuis.*')]"><Icon name="book" />{{ u.role === 'admin' ? 'Semua Ruang Belajar' : 'Ruang Belajar' }}</Link>
            <Link :href="route('jadwal')" :class="['nav', on('jadwal')]"><Icon name="calendar" />Jadwal</Link>
            <Link :href="route('pengumuman')" :class="['nav', on('pengumuman')]"><Icon name="megaphone" />Pengumuman</Link>
            <template v-if="u.role === 'admin'">
                <div class="judul-grup">Data sekolah</div>
                <Link :href="route('admin.pengguna', 'siswa')" :class="['nav', onPath('/admin/pengguna/siswa')]"><Icon name="users" />Siswa</Link>
                <Link :href="route('admin.pengguna', 'guru')" :class="['nav', onPath('/admin/pengguna/guru')]"><Icon name="user" />Guru</Link>
                <Link :href="route('admin.kelas')" :class="['nav', on('admin.kelas')]"><Icon name="grid" />Kelas</Link>
                <Link :href="route('admin.mapel')" :class="['nav', on('admin.mapel')]"><Icon name="layers" />Mata Pelajaran</Link>
                <Link v-if="pakaiJurusan" :href="route('admin.jurusan')" :class="['nav', on('admin.jurusan')]"><Icon name="flag" />Jurusan</Link>
                <Link :href="route('admin.ruang')" :class="['nav', on('admin.ruang')]"><Icon name="clipboard" />Pembagian Mengajar</Link>
                <div class="judul-grup">Laporan</div>
                <Link :href="route('admin.rekap.presensi')" :class="['nav', on('admin.rekap.presensi')]"><Icon name="check-square" />Rekap Presensi</Link>
                <Link :href="route('admin.rekap.nilai')" :class="['nav', on('admin.rekap.nilai')]"><Icon name="award" />Leger Nilai</Link>
                <Link :href="route('admin.rekap.keaktifan')" :class="['nav', on('admin.rekap.keaktifan')]"><Icon name="chart" />Keaktifan Siswa</Link>
                <Link :href="route('admin.sekolah')" :class="['nav', on('admin.sekolah')]"><Icon name="settings" />Pengaturan</Link>
            </template>
            <div class="bawah">
                <Link :href="route('profil')" class="akun" style="text-decoration:none">
                    <span class="avatar">{{ u.inisial }}</span>
                    <span style="min-width:0"><b class="potong">{{ u.name }}</b><small>{{ u.role_label }}{{ u.kelas ? ' · ' + u.kelas : '' }}</small></span>
                </Link>
                <TombolAksi :href="route('logout')" kelas="btn garis kecil"><Icon name="logout" :size="16" />Keluar</TombolAksi>
            </div>
        </aside>
        <main class="utama">
            <Flash />
            <slot />
        </main>
    </div>
</template>
