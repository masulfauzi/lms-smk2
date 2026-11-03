<script setup>
import { computed, reactive } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Icon from '@/Components/Icon.vue';
import Kosong from '@/Components/Kosong.vue';
import Paginasi from '@/Components/Paginasi.vue';
import TombolAksi from '@/Components/TombolAksi.vue';
import TombolHapus from '@/Components/TombolHapus.vue';
import { formUbah } from '@/lib/formUbah';

const props = defineProps({ role: String, query: Object, daftar: Object, kelas: Array, ubah: Object });
const siswa = computed(() => props.role === 'siswa');
const nomor = computed(() => (siswa.value ? 'NISN' : 'NIP'));

const filter = reactive({ kelas: props.query.kelas ?? '', cari: props.query.cari ?? '' });
const saring = () => router.get(route('admin.pengguna', props.role), Object.fromEntries(Object.entries(filter).filter(([, v]) => v !== '')));
const urlUbah = (id) => route('admin.pengguna', { ...props.query, role: props.role, ubah: id });

const form = formUbah(() => props.ubah, (u) => ({
    id: u?.id ?? '', name: u?.name ?? '', username: u?.username ?? '', jk: u?.jk ?? '',
    kelas_id: u?.kelas_id ?? (Number(props.query.kelas) || (props.kelas[0]?.id ?? '')),
    email: u?.email ?? '', password: '', aktif: u ? !!u.aktif : true,
}));
const simpan = () => form.post(route('admin.pengguna', props.role), { preserveScroll: true });

const impor = useForm({ csv: null });
const kirimImpor = () => impor.post(route('admin.pengguna.impor', props.role), { preserveScroll: true, onSuccess: () => impor.reset() });
</script>

<template>
    <Head :title="role.charAt(0).toUpperCase() + role.slice(1)" />
    <AppLayout>
        <div class="kepala">
            <div><h1><span class="sorot">{{ siswa ? 'Data siswa' : 'Data guru' }}</span></h1><p>Login memakai {{ nomor }}. Password awal = {{ nomor }} bila dikosongkan saat impor.</p></div>
            <form class="inline-form" @submit.prevent="saring">
                <div v-if="siswa" class="field"><select v-model="filter.kelas" name="kelas" @change="saring"><option value="">Semua kelas</option><option v-for="k in kelas" :key="k.id" :value="String(k.id)">{{ k.nama }}</option></select></div>
                <div class="field"><input v-model="filter.cari" type="search" name="cari" placeholder="Cari nama / nomor"></div>
                <button type="submit" class="btn garis"><Icon name="search" :size="16" /></button>
            </form>
        </div>
        <div class="grid g-utama">
            <div class="kartu rapat">
                <div class="tabel-wrap"><table>
                    <thead><tr><th>Nama</th><th>{{ nomor }}</th><th v-if="siswa">Kelas</th><th>L/P</th><th>Status</th><th>Terakhir aktif</th><th /></tr></thead>
                    <tbody>
                        <tr v-for="p in daftar.data" :key="p.id"><td class="tebal">{{ p.name }}</td><td>{{ p.username }}</td><td v-if="siswa">{{ p.kelas ?? '-' }}</td><td>{{ p.jk ?? '-' }}</td>
                            <td><span v-if="p.aktif" class="lencana hijau">Aktif</span><span v-else class="lencana">Nonaktif</span></td>
                            <td class="kecil-redup">{{ p.terakhir_aktif ?? '-' }}</td>
                            <td class="angka" style="white-space:nowrap">
                                <Link class="ikon-btn" :href="urlUbah(p.id)" title="Ubah"><Icon name="edit" :size="16" /></Link>
                                <TombolAksi :href="route('admin.pengguna.reset', p.id)" kelas="ikon-btn" title="Reset password" style="display:inline-flex" :konfirmasi="`Reset password ${p.name} menjadi ${nomor}-nya?`"><Icon name="key" :size="16" /></TombolAksi>
                                <TombolHapus :url="route('admin.pengguna.hapus', p.id)" :pesan="`Hapus ${p.name}? Semua tugas, presensi, dan nilainya ikut terhapus. Untuk lulus/pindah, cukup nonaktifkan.`" />
                            </td></tr>
                        <tr v-if="!daftar.data.length"><td colspan="7"><Kosong ikon="users" judul="Tidak ada data" /></td></tr>
                    </tbody>
                </table></div>
                <Paginasi :paginator="daftar" />
            </div>
            <div>
                <form class="kartu" @submit.prevent="simpan">
                    <h2><Icon :name="ubah ? 'edit' : 'plus'" :size="18" />{{ ubah ? 'Ubah ' + role : 'Tambah ' + role }}</h2>
                    <div class="field"><label>Nama lengkap</label><input v-model="form.name" type="text" name="name" required></div>
                    <div class="field"><label>{{ nomor }} (untuk login)</label><input v-model="form.username" type="text" name="username" required></div>
                    <div class="baris-form">
                        <div class="field"><label>L/P</label><select v-model="form.jk" name="jk"><option value="">-</option><option value="L">L</option><option value="P">P</option></select></div>
                        <div v-if="siswa" class="field"><label>Kelas</label><select v-model="form.kelas_id" name="kelas_id" required><option v-for="k in kelas" :key="k.id" :value="k.id">{{ k.nama }}</option></select></div>
                    </div>
                    <div class="field"><label>Email (opsional)</label><input v-model="form.email" type="email" name="email"></div>
                    <div class="field"><label>Password {{ ubah ? '(kosongkan bila tidak diganti)' : '' }}</label><input v-model="form.password" type="text" name="password" :required="!ubah" minlength="6" autocomplete="new-password"></div>
                    <div class="field"><label class="cek"><input v-model="form.aktif" type="checkbox" name="aktif"> Akun aktif</label></div>
                    <button type="submit" class="btn" :disabled="form.processing"><Icon name="check" :size="16" />Simpan</button> <Link v-if="ubah" class="btn garis" :href="route('admin.pengguna', role)">Batal</Link>
                </form>
                <form class="kartu" @submit.prevent="kirimImpor">
                    <h2><Icon name="upload" :size="18" />Impor dari Excel (CSV)</h2>
                    <p class="kecil-redup">Kolom: <code>nama;{{ siswa ? 'nisn' : 'nip' }};jk;kelas;password</code>. Simpan dari Excel sebagai CSV. {{ siswa ? 'Nama kelas harus sama persis dengan data kelas.' : '' }}</p>
                    <div class="field"><input type="file" name="csv" accept=".csv,.txt" required @input="impor.csv = $event.target.files[0]"></div>
                    <div style="display:flex;gap:8px;flex-wrap:wrap"><button type="submit" class="btn garis" :disabled="impor.processing"><Icon name="upload" :size="16" />Impor</button><a class="btn garis" :href="route('admin.pengguna.contoh', role)"><Icon name="download" :size="16" />Contoh CSV</a></div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
