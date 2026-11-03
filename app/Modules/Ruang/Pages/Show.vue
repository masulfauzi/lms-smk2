<script setup>
import { computed } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Icon from '@/Components/Icon.vue';
import Kosong from '@/Components/Kosong.vue';
import RuangKepala from '../Components/RuangKepala.vue';

defineProps({ ruang: Object, deskripsi_html: String, jadwal: Array, pertemuan: Array });
const siswa = computed(() => usePage().props.auth.user.role === 'siswa');
</script>

<template>
    <Head :title="ruang.judul" />
    <AppLayout>
        <RuangKepala :ruang="ruang" />
        <div class="grid g-utama">
            <div>
                <div v-if="!pertemuan.length" class="kartu"><Kosong judul="Belum ada pertemuan" :teks="siswa ? 'Guru belum mengunggah materi.' : 'Klik tombol &quot;Pertemuan&quot; untuk menambah materi pertama.'" /></div>
                <div v-else class="pertemuan">
                    <div v-for="p in pertemuan" :key="p.id" :class="['pt', p.beres ? 'selesai' : '', p.terbit ? '' : 'draf']">
                        <div class="no"><small>PERT.</small>{{ p.urutan }}</div>
                        <h3><Link :href="route('pertemuan.show', [ruang.id, p.id])">{{ p.judul }}</Link></h3>
                        <div class="kecil-redup">{{ p.tanggal }}</div>
                        <div class="meta">
                            <span v-if="!p.terbit" class="lencana">Draf</span>
                            <span v-if="p.video" class="lencana biru"><Icon name="video" :size="12" />Video</span>
                            <span v-if="p.lampiran" class="lencana biru"><Icon name="paperclip" :size="12" />Lampiran</span>
                            <span v-if="p.presensi" :class="['lencana', p.presensi.kelas]"><Icon v-if="p.presensi.ikon" name="check-square" :size="12" />{{ p.presensi.label }}</span>
                            <span v-if="p.beres" class="lencana hijau"><Icon name="check" :size="12" />Sudah dipelajari</span>
                        </div>
                    </div>
                </div>
            </div>
            <div>
                <div v-if="deskripsi_html" class="catatan" style="margin-bottom:16px"><div class="tulisan" style="font-size:19px;color:var(--margin)">Catatan guru</div><div style="font-size:14px" v-html="deskripsi_html" /></div>
                <div class="kartu">
                    <h2><Icon name="calendar" :size="18" />Jadwal kelas daring</h2>
                    <div v-for="j in jadwal" :key="j.id" class="slot" :style="{ borderLeftColor: ruang.warna }">
                        <b>{{ j.waktu }}</b>
                        <a v-if="j.link_meet" class="kecil-redup" :href="j.link_meet" target="_blank" rel="noopener">{{ j.link_pendek }}</a><small v-else>Belum ada link</small>
                    </div>
                    <p v-if="!jadwal.length" class="kecil-redup">Jadwal belum diatur.</p>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
