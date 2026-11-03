<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Icon from '@/Components/Icon.vue';
import Kosong from '@/Components/Kosong.vue';
import RuangKepala from '@modules/Ruang/Components/RuangKepala.vue';

defineProps({ ruang: Object, tugas: Array });
</script>

<template>
    <Head :title="'Tugas · ' + ruang.judul" />
    <AppLayout>
        <RuangKepala :ruang="ruang" />
        <div class="kartu">
            <ul v-if="tugas.length" class="daftar">
                <li v-for="t in tugas" :key="t.id">
                    <span :class="['ikon', t.lewat ? '' : 'kuning']"><Icon name="clipboard" :size="18" /></span>
                    <span class="teks"><b class="potong"><Link :href="route('tugas.show', [ruang.id, t.id])">{{ t.judul }}</Link></b>
                        <small>{{ t.keterangan }}</small></span>
                    <span v-for="(l, i) in t.lencana" :key="i" :class="['lencana', l[0]]">{{ l[1] }}</span>
                </li>
            </ul>
            <Kosong v-else ikon="clipboard" judul="Belum ada tugas" />
        </div>
    </AppLayout>
</template>
