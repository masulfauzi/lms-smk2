<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Icon from '@/Components/Icon.vue';
import Kosong from '@/Components/Kosong.vue';
import Paginasi from '@/Components/Paginasi.vue';
import RuangKepala from '@modules/Ruang/Components/RuangKepala.vue';
import Pesan from '../Components/Pesan.vue';
import FormBalas from '../Components/FormBalas.vue';

const props = defineProps({ ruang: Object, topik: Object });
const form = useForm({ isi: '' });
const kirim = () => form.post(route('ruang.diskusi', props.ruang.id), { preserveScroll: true, onSuccess: () => form.reset() });
</script>

<template>
    <Head :title="'Diskusi · ' + ruang.judul" />
    <AppLayout>
        <RuangKepala :ruang="ruang" />
        <div class="grid g-utama">
            <div class="kartu">
                <form @submit.prevent="kirim">
                    <div class="field"><label>Tulis pertanyaan atau bahan diskusi</label><textarea v-model="form.isi" name="isi" required maxlength="3000" style="min-height:80px" placeholder="Contoh: Pak/Bu, maksud soal nomor 3 bagaimana?" /></div>
                    <button type="submit" class="btn" :disabled="form.processing"><Icon name="message" :size="16" />Kirim</button>
                </form>
                <div class="mt2">
                    <Pesan v-for="d in topik.data" :key="d.id" :pesan="d" :url-hapus="route('diskusi.hapus', [ruang.id, d.id])" konfirmasi="Hapus pesan ini beserta balasannya?">
                        <div class="balas">
                            <Pesan v-for="b in d.balasan" :key="b.id" :pesan="b" :url-hapus="route('diskusi.hapus', [ruang.id, b.id])" konfirmasi="Hapus balasan ini?" kecil />
                            <FormBalas :ruang-id="ruang.id" :parent-id="d.id" />
                        </div>
                    </Pesan>
                    <Kosong v-if="!topik.data.length" ikon="message" judul="Belum ada diskusi" teks="Jadilah yang pertama bertanya." />
                    <Paginasi :paginator="topik" />
                </div>
            </div>
            <div class="catatan"><div class="tulisan" style="font-size:19px;color:var(--margin)">Etika diskusi</div>
                <p style="font-size:14px">Gunakan bahasa yang sopan, tanyakan hal yang berkaitan dengan pelajaran, dan saling bantu menjawab pertanyaan teman.</p></div>
        </div>
    </AppLayout>
</template>
