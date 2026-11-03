import { watch } from 'vue';
import { useForm, usePage } from '@inertiajs/vue3';

/**
 * Form tambah/ubah untuk halaman yang memuat ulang dirinya sendiri setelah simpan
 * (pola ?ubah=ID, ?soal=ID, koreksi siswa berikutnya, dst.).
 *
 * isi(data) mengembalikan nilai form dari data yang sedang diubah (null = form tambah).
 * Setiap respons sukses (tanpa error validasi / flash error) form diisi ulang dari props terbaru,
 * sama seperti form Blade yang kosong lagi setelah redirect. Bila gagal, isian pengguna dipertahankan
 * (pengganti old() / withInput()).
 */
export function formUbah(ambil, isi) {
    const page = usePage();
    const form = useForm(isi(ambil()));
    watch(() => page.props, (props) => {
        const gagal = Object.keys(props.errors ?? {}).length > 0 || !!props.flash?.error;
        if (gagal) return;
        form.defaults(isi(ambil()));
        form.reset();
        form.clearErrors();
    });
    return form;
}
