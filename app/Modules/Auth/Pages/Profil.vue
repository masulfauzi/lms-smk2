<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Icon from '@/Components/Icon.vue';

defineProps({ akun: Object });

const form = useForm({ password_lama: '', password: '', password_confirmation: '' });
const simpan = () => form.post(route('profil'), { preserveScroll: true, onSuccess: () => form.reset() });
</script>

<template>
    <Head title="Profil" />
    <AppLayout>
        <div class="kepala"><div><h1><span class="sorot">Profil saya</span></h1><p>Data akun dan penggantian password.</p></div></div>
        <div class="grid g2">
            <div class="kartu">
                <h2><Icon name="user" :size="18" />Data akun</h2>
                <table>
                    <tr><td class="kecil-redup">Nama</td><td class="tebal">{{ akun.name }}</td></tr>
                    <tr><td class="kecil-redup">{{ akun.role === 'siswa' ? 'NISN' : (akun.role === 'guru' ? 'NIP' : 'Username') }}</td><td>{{ akun.username }}</td></tr>
                    <tr><td class="kecil-redup">Peran</td><td>{{ akun.role_label }}</td></tr>
                    <tr v-if="akun.kelas"><td class="kecil-redup">Kelas</td><td>{{ akun.kelas }}</td></tr>
                    <tr v-if="akun.email"><td class="kecil-redup">Email</td><td>{{ akun.email }}</td></tr>
                </table>
                <p class="kecil-redup mt">Perubahan nama atau NIP/NISN dilakukan oleh admin sekolah.</p>
            </div>
            <div class="kartu">
                <h2><Icon name="key" :size="18" />Ganti password</h2>
                <form @submit.prevent="simpan">
                    <div class="field"><label>Password lama</label><input v-model="form.password_lama" type="password" name="password_lama" required autocomplete="current-password"></div>
                    <div class="field"><label>Password baru</label><input v-model="form.password" type="password" name="password" required minlength="6" autocomplete="new-password"><small>Minimal 6 karakter.</small></div>
                    <div class="field"><label>Ulangi password baru</label><input v-model="form.password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password"></div>
                    <button type="submit" class="btn" :disabled="form.processing"><Icon name="check" :size="16" />Simpan password</button>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
