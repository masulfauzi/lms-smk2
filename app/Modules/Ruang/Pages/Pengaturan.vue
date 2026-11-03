<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Icon from '@/Components/Icon.vue';
import RuangKepala from '../Components/RuangKepala.vue';

const props = defineProps({ ruang: Object, pengaturan: Object, jadwal: Array });
const form = useForm({
    ...props.pengaturan,
    deskripsi: props.pengaturan.deskripsi ?? '',
    link: Object.fromEntries(props.jadwal.map((j) => [j.id, j.link_meet ?? ''])),
});
const simpan = () => form.post(route('ruang.pengaturan', props.ruang.id), { preserveScroll: true });
</script>

<template>
    <Head :title="'Pengaturan · ' + ruang.judul" />
    <AppLayout>
        <RuangKepala :ruang="ruang" />
        <form class="grid g2" @submit.prevent="simpan">
            <div class="kartu">
                <h2><Icon name="settings" :size="18" />Ruang belajar</h2>
                <div class="field"><label>Catatan / deskripsi untuk siswa</label><textarea v-model="form.deskripsi" name="deskripsi" maxlength="2000" placeholder="Misal: aturan pengumpulan tugas, buku pegangan, kontak guru." /></div>
                <div class="field"><label>KKM</label><input v-model="form.kkm" type="number" name="kkm" min="0" max="100" required></div>
                <label>Bobot nilai akhir (total harus 100%)</label>
                <div class="baris-form">
                    <div class="field"><small>Tugas %</small><input v-model="form.bobot_tugas" type="number" name="bobot_tugas" min="0" max="100" required></div>
                    <div class="field"><small>Kuis %</small><input v-model="form.bobot_kuis" type="number" name="bobot_kuis" min="0" max="100" required></div>
                    <div class="field"><small>Kehadiran %</small><input v-model="form.bobot_kehadiran" type="number" name="bobot_kehadiran" min="0" max="100" required></div>
                </div>
            </div>
            <div class="kartu">
                <h2><Icon name="video" :size="18" />Link kelas daring per jadwal</h2>
                <div v-for="j in jadwal" :key="j.id" class="field"><label>{{ j.waktu }}</label>
                    <input v-model="form.link[j.id]" type="url" :name="`link[${j.id}]`" placeholder="https://meet.google.com/..."></div>
                <p v-if="!jadwal.length" class="kecil-redup">Jadwal ruang belajar ini belum diatur admin sekolah.</p>
                <button type="submit" class="btn" :disabled="form.processing"><Icon name="check" :size="16" />Simpan pengaturan</button>
            </div>
        </form>
    </AppLayout>
</template>
