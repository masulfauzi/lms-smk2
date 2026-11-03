<script setup>
import { computed } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Icon from '@/Components/Icon.vue';
import Kosong from '@/Components/Kosong.vue';
import RuangKepala from '@modules/Ruang/Components/RuangKepala.vue';

defineProps({ ruang: Object, kuis: Array });
const siswa = computed(() => usePage().props.auth.user.role === 'siswa');
</script>

<template>
    <Head :title="'Kuis · ' + ruang.judul" />
    <AppLayout>
        <RuangKepala :ruang="ruang" />
        <div class="kartu">
            <ul v-if="kuis.length" class="daftar">
                <li v-for="k in kuis" :key="k.id">
                    <span :class="['ikon', k.berlangsung ? 'hijau' : '']"><Icon name="quiz" :size="18" /></span>
                    <span class="teks"><b class="potong"><Link :href="route(siswa ? 'kuis.show' : 'kuis.edit', [ruang.id, k.id])">{{ k.judul }}</Link></b>
                        <small>{{ k.keterangan }}</small></span>
                    <span :class="['lencana', k.status[0]]">{{ k.status[1] }}</span>
                    <template v-if="siswa">
                        <span v-if="k.hasil" class="lencana hijau">{{ k.hasil }}</span>
                        <Link :class="['btn kecil', k.kerjakan ? 'stabilo' : 'garis']" :href="route('kuis.show', [ruang.id, k.id])">{{ k.kerjakan ? 'Kerjakan' : 'Lihat' }}</Link>
                    </template>
                    <template v-else>
                        <span class="lencana biru">{{ k.selesai_count }} mengerjakan</span>
                        <Link class="btn kecil garis" :href="route('kuis.show', [ruang.id, k.id])">Hasil</Link>
                    </template>
                </li>
            </ul>
            <Kosong v-else ikon="quiz" judul="Belum ada kuis" />
        </div>
    </AppLayout>
</template>
