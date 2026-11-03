<script setup>
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import Icon from './Icon.vue';

// Pengganti partials/flash.blade.php: pesan sukses, gagal, dan daftar error validasi
const page = usePage();
const flash = computed(() => page.props.flash ?? {});
const errors = computed(() => Object.values(page.props.errors ?? {}));
</script>

<template>
    <div v-if="flash.ok" class="flash ok"><Icon name="check" :size="18" /><div>{{ flash.ok }}</div></div>
    <div v-if="flash.error" class="flash error"><Icon name="alert" :size="18" /><div>{{ flash.error }}</div></div>
    <div v-if="errors.length" class="flash error"><Icon name="alert" :size="18" /><ul><li v-for="(e, i) in errors" :key="i">{{ e }}</li></ul></div>
</template>
