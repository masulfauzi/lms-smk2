<script setup>
import { useForm } from '@inertiajs/vue3';

const props = defineProps({ ruangId: Number, parentId: Number });
const form = useForm({ isi: '', parent_id: props.parentId });
const balas = () => form.post(route('ruang.diskusi', props.ruangId), { preserveScroll: true, onSuccess: () => form.reset('isi') });
</script>

<template>
    <form class="inline-form" style="margin-top:8px" @submit.prevent="balas">
        <div class="field" style="flex:1;min-width:180px"><input v-model="form.isi" type="text" name="isi" required maxlength="3000" placeholder="Tulis balasan..."></div>
        <button type="submit" class="btn kecil garis" :disabled="form.processing">Balas</button>
    </form>
</template>
