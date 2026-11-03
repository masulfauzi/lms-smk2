<script setup>
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';

// Pengganti partials/paginasi.blade.php; menerima paginator Laravel (->paginate()) apa adanya
const props = defineProps({ paginator: { type: Object, required: true } });
const tautan = computed(() => props.paginator.links ?? []);
const ada = computed(() => (props.paginator.last_page ?? 1) > 1);
</script>

<template>
    <nav v-if="ada" class="paginasi" aria-label="Halaman">
        <template v-for="(l, i) in tautan" :key="i">
            <template v-if="i === 0">
                <span v-if="!l.url" class="mati">&lsaquo;</span><Link v-else :href="l.url" rel="prev" preserve-scroll>&lsaquo;</Link>
            </template>
            <template v-else-if="i === tautan.length - 1">
                <span v-if="!l.url" class="mati">&rsaquo;</span><Link v-else :href="l.url" rel="next" preserve-scroll>&rsaquo;</Link>
            </template>
            <span v-else-if="!l.url" class="mati">{{ l.label }}</span>
            <span v-else-if="l.active" class="on">{{ l.label }}</span>
            <Link v-else :href="l.url" preserve-scroll>{{ l.label }}</Link>
        </template>
    </nav>
</template>
