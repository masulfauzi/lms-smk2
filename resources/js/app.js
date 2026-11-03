import '../css/app.css';
import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { ZiggyVue } from 'ziggy-js';

// Halaman ada di modul: 'Kuis/Kerjakan' → app/Modules/Kuis/Pages/Kerjakan.vue.
// Halaman bersama (tanpa modul, mis. 'Error') ada di resources/js/Pages.
const halamanModul = import.meta.glob('../../app/Modules/*/Pages/**/*.vue');
const halamanBersama = import.meta.glob('./Pages/**/*.vue');

function cari(nama) {
    const [modul, ...sisa] = nama.split('/');
    const muat = sisa.length
        ? halamanModul[`../../app/Modules/${modul}/Pages/${sisa.join('/')}.vue`]
        : halamanBersama[`./Pages/${nama}.vue`];
    if (!muat) throw new Error(`Halaman Inertia tidak ditemukan: ${nama}`);
    return muat();
}

createInertiaApp({
    title: (judul) => {
        const sekolah = document.querySelector('meta[name=sekolah]')?.content ?? '';
        return judul ? `${judul} · ${sekolah}` : sekolah;
    },
    resolve: cari,
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .mount(el);
    },
    progress: { color: '#2f5bd3' },
});
