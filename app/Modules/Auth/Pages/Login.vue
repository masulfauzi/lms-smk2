<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import Icon from '@/Components/Icon.vue';
import Flash from '@/Components/Flash.vue';

defineProps({ sekolah: Object, demo: Boolean });

const form = useForm({ username: '', password: '', ingat: false });
const masuk = () => form.post(route('login'), { onFinish: () => form.reset('password') });
</script>

<template>
    <Head title="Masuk" />
    <div class="login-halaman">
        <section class="login-kiri">
            <div class="merek" style="border:0;padding:0">
                <div class="logo"><Icon name="school" :size="22" /></div>
                <div><b>{{ sekolah.nama }}</b><small>NPSN {{ sekolah.npsn }}</small></div>
            </div>
            <div>
                <div class="tulisan">Belajar dari rumah,</div>
                <h1>tetap <span>terarah</span> bersama guru di kelas daring.</h1>
                <p>Materi, presensi, tugas, kuis, dan nilai jenjang {{ sekolah.jenjang }} ada di satu tempat.</p>
                <div class="login-poin">
                    <div><Icon name="video" />Materi teks, video, dan lampiran per pertemuan</div>
                    <div><Icon name="check-square" />Presensi daring dengan jam buka-tutup</div>
                    <div><Icon name="clipboard" />Kumpulkan tugas dan lihat nilai serta komentar guru</div>
                    <div><Icon name="quiz" />Kuis berwaktu dengan nilai otomatis</div>
                </div>
            </div>
            <small style="color:#8794c4;position:relative;z-index:1">{{ sekolah.alamat }}</small>
        </section>
        <section class="login-kanan">
            <div class="login-kotak">
                <h2><span class="sorot">Masuk</span></h2>
                <p style="color:var(--redup)">Gunakan NIP (guru) atau NISN (siswa) sebagai nama pengguna.</p>
                <Flash />
                <form class="mt" @submit.prevent="masuk">
                    <div class="field">
                        <label for="username">NIP / NISN</label>
                        <input id="username" v-model="form.username" type="text" name="username" autocomplete="username" required autofocus>
                    </div>
                    <div class="field">
                        <label for="password">Password</label>
                        <input id="password" v-model="form.password" type="password" name="password" autocomplete="current-password" required>
                    </div>
                    <div class="field"><label class="cek"><input v-model="form.ingat" type="checkbox" name="ingat"> Ingat saya di perangkat ini</label></div>
                    <button type="submit" class="btn blok" :disabled="form.processing"><Icon name="arrow" :size="18" />Masuk</button>
                </form>
                <div v-if="demo" class="demo kartu" style="padding:12px 14px">
                    <b style="color:var(--tinta)">Akun demo</b>
                    <table>
                        <tr><td>Admin</td><td><code>admin</code></td><td><code>admin123</code></td></tr>
                        <tr><td>Guru</td><td><code>guru</code></td><td><code>guru123</code></td></tr>
                        <tr><td>Siswa</td><td><code>siswa</code></td><td><code>siswa123</code></td></tr>
                    </table>
                </div>
            </div>
        </section>
    </div>
</template>
