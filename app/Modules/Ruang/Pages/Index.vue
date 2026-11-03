<script setup>
import { computed } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Icon from '@/Components/Icon.vue';
import Kosong from '@/Components/Kosong.vue';

defineProps({ kelasList: Array, kelasId: [String, Number], ruang: Array });
const u = computed(() => usePage().props.auth.user);
const keterangan = computed(() => (u.value.role === 'siswa' ? 'Mata pelajaran kelas ' + (u.value.kelas ?? '-')
    : (u.value.role === 'guru' ? 'Mapel dan kelas yang Anda ampu.' : 'Seluruh ruang belajar semester aktif.')));
const pilihKelas = (e) => router.get(route('ruang'), e.target.value ? { kelas: e.target.value } : {});
</script>

<template>
    <Head title="Ruang Belajar" />
    <AppLayout>
        <div class="kepala">
            <div><h1><span class="sorot">Ruang belajar</span></h1><p>{{ keterangan }}</p></div>
            <form v-if="u.role === 'admin'" class="inline-form" @submit.prevent><div class="field"><select name="kelas" :value="String(kelasId)" @change="pilihKelas"><option value="">Semua kelas</option>
                <option v-for="k in kelasList" :key="k.id" :value="String(k.id)">{{ k.nama }}</option>
            </select></div></form>
        </div>
        <div v-if="!ruang.length" class="kartu"><Kosong judul="Belum ada ruang belajar" :teks="u.role === 'admin' ? 'Atur di menu Pembagian Mengajar.' : 'Hubungi admin sekolah.'" /></div>
        <div v-else class="grid g3">
            <Link v-for="r in ruang" :key="r.id" class="mapel-kartu" :href="route('ruang.show', r.id)">
                <div class="warna" :style="{ background: r.warna }" />
                <div class="isi">
                    <h3>{{ r.mapel }}</h3>
                    <p>{{ r.keterangan }}</p>
                    <span class="lencana biru"><Icon name="book" :size="12" />{{ r.pertemuan_count }} pertemuan</span>
                </div>
            </Link>
        </div>
    </AppLayout>
</template>
