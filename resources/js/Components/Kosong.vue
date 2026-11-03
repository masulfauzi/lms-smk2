<script setup>
import Icon from './Icon.vue';

defineProps({ judul: String, teks: String, ikon: { type: String, default: 'book' } });
</script>

<template>
    <div class="kosong">
        <div class="ikon"><Icon :name="ikon" :size="24" /></div>
        <b>{{ judul }}</b>
        <div v-if="teks">{{ teks }}</div>
    </div>
</template>
