<script setup>
import { router } from '@inertiajs/vue3';

// Pengganti rekap::_pilih-kelas: ganti kelas langsung memuat ulang halaman dengan ?kelas=
const props = defineProps({ kelasList: Array, kelas: Object, rute: String });
const pilih = (e) => router.get(route(props.rute), { kelas: e.target.value });
</script>

<template>
    <form class="inline-form" @submit.prevent>
        <div class="field"><select name="kelas" :value="kelas?.id" @change="pilih"><option v-for="k in kelasList" :key="k.id" :value="k.id">{{ k.nama }}</option></select></div>
        <slot />
    </form>
</template>
