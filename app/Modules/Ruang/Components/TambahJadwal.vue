<script setup>
import { useForm } from '@inertiajs/vue3';

const props = defineProps({ ruangId: Number, hari: Object });
const form = useForm({ hari: Number(Object.keys(props.hari)[0]), jam_mulai: '07:30', jam_selesai: '09:00', link_meet: '' });
const tambah = () => form.post(route('admin.ruang.jadwal', props.ruangId), { preserveScroll: true, onSuccess: () => form.reset() });
</script>

<template>
    <details><summary class="kecil-redup" style="cursor:pointer">+ jadwal</summary>
        <form class="inline-form" style="margin-top:6px" @submit.prevent="tambah">
            <div class="field"><select v-model="form.hari" name="hari"><option v-for="(h, no) in hari" :key="no" :value="Number(no)">{{ h }}</option></select></div>
            <div class="field"><input v-model="form.jam_mulai" type="time" name="jam_mulai" required></div>
            <div class="field"><input v-model="form.jam_selesai" type="time" name="jam_selesai" required></div>
            <div class="field"><input v-model="form.link_meet" type="url" name="link_meet" placeholder="Link meet (opsional)"></div>
            <button type="submit" class="btn kecil" :disabled="form.processing">Tambah</button>
        </form>
    </details>
</template>
