<script setup>
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import Icon from '@/Components/Icon.vue';
import Kosong from '@/Components/Kosong.vue';
import Paginasi from '@/Components/Paginasi.vue';
import TombolHapus from '@/Components/TombolHapus.vue';

const props = defineProps({ daftar: Object, kelas: Array });
const role = computed(() => usePage().props.auth.user.role);

const form = useForm({ judul: '', isi: '', sasaran: role.value === 'admin' ? 'semua' : 'siswa', kelas_id: role.value === 'guru' ? (props.kelas[0]?.id ?? '') : '', disematkan: false });
const kirim = () => form.post(route('pengumuman'), { preserveScroll: true, onSuccess: () => form.reset('judul', 'isi', 'disematkan') });
</script>

<template>
    <Head title="Pengumuman" />
    <AppLayout>
        <div class="kepala"><div><h1><span class="sorot">Pengumuman</span></h1><p>Informasi dari sekolah dan guru.</p></div></div>
        <div :class="['grid', role === 'siswa' ? '' : 'g-utama']">
            <div class="kartu">
                <article v-for="p in daftar.data" :key="p.id" style="padding:14px 0;border-bottom:1px dashed var(--garis-2)">
                    <div style="display:flex;gap:10px;align-items:flex-start">
                        <div style="flex:1;min-width:0">
                            <h3 style="font-size:16px;display:flex;gap:6px;align-items:center;flex-wrap:wrap"><span v-if="p.disematkan" class="lencana kuning"><Icon name="pin" :size="12" />Disematkan</span>{{ p.judul }}</h3>
                            <div class="kecil-redup">{{ p.keterangan }}</div>
                        </div>
                        <TombolHapus v-if="p.boleh_hapus" :url="route('pengumuman.hapus', p.id)" pesan="Hapus pengumuman ini?" />
                    </div>
                    <div class="materi" style="font-size:14.5px;margin-top:8px" v-html="p.isi_html" />
                </article>
                <Kosong v-if="!daftar.data.length" ikon="megaphone" judul="Belum ada pengumuman" />
                <Paginasi :paginator="daftar" />
            </div>
            <div v-if="role !== 'siswa'" class="kartu">
                <h2><Icon name="plus" :size="18" />Buat pengumuman</h2>
                <form @submit.prevent="kirim">
                    <div class="field"><label>Judul</label><input v-model="form.judul" type="text" name="judul" required maxlength="160"></div>
                    <div class="field"><label>Isi</label><textarea v-model="form.isi" name="isi" required /></div>
                    <template v-if="role === 'admin'">
                        <div class="field"><label>Ditujukan untuk</label>
                            <select v-model="form.sasaran" name="sasaran"><option value="semua">Semua warga sekolah</option><option value="guru">Guru saja</option><option value="siswa">Siswa saja</option></select></div>
                        <div class="field"><label>Khusus kelas (opsional)</label>
                            <select v-model="form.kelas_id" name="kelas_id"><option value="">Semua kelas</option><option v-for="k in kelas" :key="k.id" :value="k.id">{{ k.nama }}</option></select></div>
                        <div class="field"><label class="cek"><input v-model="form.disematkan" type="checkbox" name="disematkan"> Sematkan di atas</label></div>
                    </template>
                    <div v-else class="field"><label>Untuk siswa kelas</label>
                        <select v-model="form.kelas_id" name="kelas_id" required><option v-for="k in kelas" :key="k.id" :value="k.id">{{ k.nama }}</option></select></div>
                    <button type="submit" class="btn" :disabled="form.processing"><Icon name="megaphone" :size="16" />Terbitkan</button>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
