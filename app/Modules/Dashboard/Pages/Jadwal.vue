<script setup>
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Icon from '@/Components/Icon.vue';

defineProps({ kelasList: Array, kelasId: Number, hari: Array });
const pilihKelas = (e) => router.get(route('jadwal'), { kelas: e.target.value }, { preserveScroll: true });
</script>

<template>
    <Head title="Jadwal" />
    <AppLayout>
        <div class="kepala">
            <div><h1><span class="sorot">Jadwal pelajaran</span></h1><p>Jadwal kelas daring per minggu beserta link pertemuan virtual.</p></div>
            <form v-if="kelasList.length" class="inline-form" @submit.prevent><div class="field"><select name="kelas" :value="kelasId" @change="pilihKelas">
                <option v-for="k in kelasList" :key="k.id" :value="k.id">{{ k.nama }}</option>
            </select></div></form>
        </div>
        <div class="jadwal-hari">
            <div v-for="h in hari" :key="h.no" class="kartu" :style="h.hari_ini ? 'border-color:var(--tinta);border-width:2px' : ''">
                <h2>{{ h.nama }} <span v-if="h.hari_ini" class="lencana kuning kanan">Hari ini</span></h2>
                <div v-for="j in h.slot" :key="j.id" class="slot" :style="{ borderLeftColor: j.warna }">
                    <b>{{ j.mapel }}</b>
                    <small>{{ j.jam }} · {{ j.keterangan }}</small>
                    <div v-if="j.link_meet"><a class="kecil-redup" :href="j.link_meet" target="_blank" rel="noopener" style="display:inline-flex;gap:4px;align-items:center"><Icon name="video" :size="13" />Link kelas daring</a></div>
                </div>
                <p v-if="!h.slot.length" class="kecil-redup">Kosong</p>
            </div>
        </div>
    </AppLayout>
</template>
