# CLAUDE.md

Panduan untuk Claude Code saat bekerja di repo ini.

## Ringkasan

LMS Pembelajaran Jarak Jauh (PJJ) untuk sekolah SD/SMP/SMA/SMK. Laravel 12, PHP ^8.2, MySQL/MariaDB, tampilan **Inertia.js 2 + Vue 3** (JavaScript, `<script setup>`). Tiga peran: `admin`, `guru`, `siswa`. Seluruh UI, kode, nama tabel/kolom/route/method memakai **Bahasa Indonesia**: ikuti konvensi ini (`simpan`, `hapus`, `kumpul`, `tenggat`, `terbit`, ...).

Dijalankan di XAMPP macOS: `/Applications/XAMPP/xamppfiles/htdocs/lms-smk2`, diakses lewat `http://localhost/lms-smk2` (root `index.php` meneruskan ke `public/index.php`) atau `php artisan serve`.

## Perintah

```bash
composer install
php artisan migrate:fresh --seed   # reset DB + data contoh (merusak data!)
php artisan serve
php artisan test                   # tests/ baru berisi contoh bawaan Laravel
vendor/bin/pint                    # format kode
php artisan optimize:clear         # bersihkan cache, termasuk cache 'pengaturan'
npm install
npm run dev                        # Vite dev server (bersama php artisan serve)
npm run build                      # WAJIB setelah mengubah .vue/.js/.css, sebelum commit
```

Akun demo (seeder): `admin/admin123`, `guru/guru123`, `siswa/siswa123`; akun lain password `rahasia123`.

## Frontend (Inertia + Vue)

- **`public/build` di-commit** agar server/komputer sekolah tidak butuh Node.js. Setiap perubahan `.vue`/`.js`/`.css` → jalankan `npm run build` dan ikut commit-kan `public/build`. Jangan commit `public/hot`.
- Root template: `resources/views/app.blade.php` (`@routes` Ziggy, `@vite`, `@inertia`). Blade lain hanya `resources/views/errors/*` untuk muat halaman penuh.
- `resources/js/app.js` me-resolve nama halaman `'Kuis/Kerjakan'` → `app/Modules/Kuis/Pages/Kerjakan.vue`; nama tanpa modul (`'Error'`) → `resources/js/Pages/`.
- Alias impor: `@` = `resources/js`, `@modules` = `app/Modules` (mis. `import RuangKepala from '@modules/Ruang/Components/RuangKepala.vue'`).
- Komponen bersama (`resources/js/Components`): `Icon` (path SVG Lucide di `resources/js/lib/ikon.js`; tambah ikon di sana), `Flash` (flash ok/error + error validasi), `Kosong`, `TombolAksi` (POST/DELETE + `confirm()`), `TombolHapus`, `Paginasi` (paginator Laravel apa adanya). Layout: `AppLayout` (bungkus halaman: `<AppLayout>...</AppLayout>`), halaman login tanpa layout.
- Nama route Laravel dipakai di Vue lewat Ziggy: `route('kuis.edit', [ruang.id, kuis.id])`, query: `route('admin.jurusan', { ubah: id })`, cek aktif: `route().current('kuis.*')`.
- CSS tetap satu file `resources/css/app.css` (kelas-kelas lama: `kartu`, `btn`, `lencana`, ...). Jangan menambahkan framework CSS.
- Shared props (`HandleInertiaRequests`): `auth.user` (id, name, role, role_label, inisial, kelas), `sekolah` (nama, jenjang), `tahun_ajaran`, `flash` (ok, error), `errors`.

## Struktur modular

Kode aplikasi ada di `app/Modules/<Modul>/` (Auth, Dashboard, Sekolah, Jurusan, Mapel, Kelas, Pengguna, Ruang, Pertemuan, Tugas, Kuis, Diskusi, Pengumuman, Rekap). Tiap modul:

```
app/Modules/Kuis/
  Controllers/KuisController.php   namespace App\Modules\Kuis\Controllers, extends App\Http\Controllers\Controller
  Models/Kuis.php, Soal.php, ...   namespace App\Modules\Kuis\Models
  Pages/Form.vue, Kerjakan.vue ... Inertia::render('Kuis/Form', [...])
  Components/*.vue                 komponen khusus modul (opsional)
  routes.php                       route modul ini
```

- **Jangan** membuat file di `app/Models`, `app/Http/Controllers` (selain base `Controller.php`), `resources/views/<fitur>`, atau view Blade di modul: tampilan selalu halaman Vue di `app/Modules/<Modul>/Pages`.
- Model ditaruh di modul pemiliknya (lihat tabel di README). Modul lain boleh memakainya lewat `use App\Modules\<Pemilik>\Models\X;`. Contoh: `User` ada di `Pengguna`, `Ruang` dan `Jadwal` ada di `Ruang`, `TahunAjaran` dan `Pengaturan` ada di `Sekolah`.
- Header ruang belajar (dipakai halaman Ruang, Pertemuan, Tugas, Kuis, Diskusi): komponen `@modules/Ruang/Components/RuangKepala.vue` dengan prop `ruang` dari `Ruang::kepala()`.
- `routes/web.php` hanya me-`require` semua `app/Modules/*/routes.php` (urutan abjad). Nama route global, jadi tetap unik lintas modul (`kuis.edit`, `admin.jurusan`, ...).
- **Middleware route**: tulis semua middleware grup dalam **satu** array, misalnya `Route::middleware(['auth', 'role', 'can:akses,ruang'])->prefix('ruang/{ruang}')`. Memanggil `->middleware()` dua kali pada satu rantai *menimpa* middleware sebelumnya, sehingga `auth` bisa hilang. Di dalam closure grup, `->middleware()` bertingkat aman.
- Migration, seeder, dan factory tetap terpusat di `database/`, tidak di dalam modul.

**Membuat modul baru** (`app/Modules/Contoh`):
1. Buat `Controllers/`, `Models/`, `Pages/`, dan `routes.php` dengan namespace `App\Modules\Contoh\...`.
2. Di `routes.php`, bungkus route dengan `Route::middleware(['auth', 'role', ...])` (tambahkan `'can:akses,ruang'` + prefix `ruang/{ruang}` untuk fitur di dalam ruang, atau `'role:admin'` + `prefix('admin')->name('admin.')` untuk fitur admin).
3. Di controller: `return Inertia::render('Contoh/Index', [...props...]);` lalu buat `Pages/Index.vue` (otomatis ditemukan oleh `app.js`).
4. Jalankan `composer dump-autoload`, `php artisan optimize:clear`, `npm run build`, lalu cek dengan `php artisan route:list`.

**Pola controller → Vue (ikuti, sudah dipakai di semua modul):**
- Props dipilih **eksplisit** (array/`->only()`/`->map()`), jangan kirim model utuh. Nilai yang butuh method model (`status()`, `lewatTenggat()`, `warna()`, `inisial()`, ...) dihitung di controller dan dikirim sebagai field.
- Tanggal/waktu diformat di **server** dengan helper (`tgl()`, `tgl_panjang()`, `sisa_waktu()`, `predikat()`, `diffForHumans()`), supaya zona waktu & bahasa sama (Asia/Jakarta, id). Tanggal untuk input form dikirim sebagai `Y-m-d` / `Y-m-d\TH:i`.
- Paginator dikirim dengan `->paginate()->through(fn ...)`; di Vue pakai `daftar.data` + `<Paginasi :paginator="daftar" />`.
- Form: `useForm()`. Upload file: `forceFormData: true`; update dengan file: `form.transform(d => ({ ...d, _method: 'put' })).post(...)`. Hapus/aksi kecil: `TombolHapus` / `TombolAksi`. Unduhan & CSV: `<a href>` biasa (bukan `<Link>`).
- `form.post/put/delete` memakai `preserveState: true`: bila server me-redirect ke **halaman yang sama** (pola `?ubah=ID`, koreksi siswa berikutnya, daftar pilihan berubah), pakai `formUbah()` dari `@/lib/formUbah` agar form diisi ulang dari props setelah sukses dan isian tetap ada bila gagal. Jangan menyalin `id` dari props sekali di `setup()` bila halaman bisa dimuat ulang dengan id lain; pakai `computed`.
- Spasi antar-elemen yang dipisah baris baru dihapus compiler Vue; tulis di satu baris (`</b> <span>`) bila spasi itu perlu tampil.

## Arsitektur

**Ruang belajar (`Ruang`)** adalah pusat domain: 1 mapel × 1 kelas × 1 tahun ajaran, diampu 1 guru (`guru_id`). Di dalamnya ada `Jadwal`, `Pertemuan` (materi + presensi), `Tugas` → `Pengumpulan`, `Kuis` → `Soal` & `Percobaan`, `Diskusi`. Siswa masuk ruang lewat `users.kelas_id`; `Ruang::siswa()` = siswa aktif di kelas tsb.

- **Tahun ajaran aktif**: `TahunAjaran::aktifId()` (di-cache statis per request). `User::ruangQuery()` sudah memfilter ruang milik user pada tahun aktif: pakai ini untuk daftar ruang.
- **Pengaturan sekolah**: key-value di tabel `pengaturan`, baca dengan `setting('kunci')`, tulis dengan `Pengaturan::simpan([...])` (otomatis membuang cache). Nilai bawaan di `Pengaturan::BAWAAN`.
- **Nilai**: semua perhitungan nilai/kehadiran ada di `App\Support\Rapor` (`Rapor::ruang()`, `Rapor::akhir()`). Jangan menduplikasi rumus di controller/view. Alpa tidak disimpan untuk siswa yang tidak presensi: dihitung dari pertemuan yang `presensi_tutup` sudah lewat.
- **Kuis**: status dari `Kuis::status()` (`draf|belum|berlangsung|selesai`). `Percobaan` menyimpan `urutan_soal`, `jawaban`, `nilai_esai` (JSON); nilai via `Percobaan::hitungNilai()` (null bila esai belum dikoreksi). Jawaban disimpan per soal via AJAX (`kuis.jawab`, pakai `lockForUpdate`); percobaan yang lewat `batas_at` ditutup otomatis (`tutupBilaHabis`). Ubah soal → `hitungUlang()` percobaan yang sudah selesai.
- **Impor soal** (`KuisController::imporSoal`): blok `1. pertanyaan` / `A. opsi` / `Kunci: A`; tanpa opsi = esai bobot 5, PG bobot 1.

## Keamanan & hak akses (wajib diikuti)

1. Semua route login ada di grup `middleware(['auth', 'role'])`. Middleware `role` (`app/Http/Middleware/Role.php`) menolak akun nonaktif, membatasi peran via `role:admin,guru`, dan memperbarui `terakhir_aktif_at` (maks 1×/5 menit).
2. Route di bawah `ruang/{ruang}/...` memakai `can:akses,ruang` (Gate di `AppServiceProvider` → `User::bolehAkses()`: admin semua, guru ruang yang diampu, siswa ruang kelasnya).
3. Setiap model anak yang di-bind bersama `{ruang}` **harus** dicek dengan `$this->milik($model, $ruang)` (base `Controller`) agar ID dari ruang lain tidak bisa diakses.
4. Siswa tidak boleh melihat konten belum terbit (`pertemuan.terbit`, `kuis.terbit`) dan hanya boleh mengakses pengumpulan miliknya.
5. Berkas disimpan di disk `local` (storage privat, `storage/app/private`) dengan path `materi/ruang-{id}`, `soal-tugas/ruang-{id}`, `tugas/{tugas_id}`. Unduhan **hanya** lewat method controller yang mengecek akses lalu memanggil `$this->kirimBerkas()` (base `Controller`): `PertemuanController::berkas`, `TugasController::berkas` / `berkasKumpulan`. Jangan lewat `storage/` publik. Validasi upload: maks 20 MB, `extensions` + `mimes` pdf,doc,docx,ppt,pptx,xls,xlsx,jpg,jpeg,png,zip,txt (lihat konstanta di `PertemuanController`/`TugasController`).
6. Teks bebas dari pengguna diubah di **server** dengan `teks($s)` (meng-escape lalu mengubah URL, `**tebal**`, paragraf), dikirim sebagai prop `*_html`, lalu dirender `v-html`. **Jangan** `v-html` untuk teks mentah dari pengguna.
7. Error untuk navigasi Inertia (403/404/500/503) dirender `resources/js/Pages/Error.vue` lewat `bootstrap/app.php` (`withExceptions()->respond()`); 419 → kembali dengan flash "Sesi berakhir". Muat halaman penuh tetap memakai `resources/views/errors/*`.
8. Login dibatasi `throttle:10,1`; akun demo di halaman login disembunyikan saat `APP_ENV=production`.

## Konvensi kode

- Nama tabel singular Bahasa Indonesia (`kelas`, `ruang`, `tugas`, `kuis`, `pengumuman`, ...), jadi setiap model mendefinisikan `protected $table`.
- Migration LMS: **satu file per tabel** (`2026_10_27_000001_create_pengaturan_table.php` … `000018_create_pengumuman_table.php`), diurutkan sesuai dependensi FK. `users.kelas_id` mendapat FK lewat `000005_add_kelas_foreign_to_users_table.php` karena `users` dibuat sebelum `kelas`. Tabel baru = file baru dengan timestamp setelah tabel induknya. Perubahan skema tabel yang sudah ada = migration baru untuk tabel itu saja (jangan ubah file lama bila sudah ada data nyata). `down()` harus membalik `up()` agar `migrate:rollback` jalan. Perbarui `DatabaseSeeder` bila perlu. Tiga migration bawaan Laravel (users, cache, jobs) dibiarkan sesuai standar Laravel.
- Seeder deterministik (`mt_srand(2026)`) dan tanggalnya relatif terhadap `now()`: pertahankan agar demo selalu terlihat "hidup".
- Fitur admin ada di modulnya masing-masing (mis. `Jurusan`, `Rekap`, `Ruang\Controllers\PembagianController`), route admin berprefiks URL `admin/` dan nama `admin.`. Validasi inline dengan `$r->validate()` (belum memakai FormRequest), pesan error Bahasa Indonesia.
- Helper global (`app/helpers.php`, autoload via composer `files`): `setting`, `tgl`, `tgl_panjang`, `sisa_waktu`, `teks`, `predikat`, `icon` (`icon()` kini hanya untuk Blade halaman error; di Vue pakai `<Icon name>`). Format tanggal Indonesia via `tgl()`, jangan format manual.
- Timezone `Asia/Jakarta`, locale `id`. Menurut `.env.example`, session & cache memakai driver `file` dan queue `database`.
- Gaya kode ringkas (variabel pendek seperti `$r`, `$u`, `$p`), ikuti gaya file yang sedang diedit.

## Git & commit

- Jangan menambahkan AI sebagai kontributor di commit: **tanpa** baris `Co-Authored-By: Claude ...` dan tanpa atribusi AI lain (misalnya "Generated with Claude Code") di pesan commit maupun deskripsi PR.
- Commit memakai identitas git milik user (`git config user.name` / `user.email`); jangan mengubah author atau konfigurasi git.
- Commit hanya bila diminta user.

## Catatan lingkungan / gotcha

- `.env` dibuat dari `.env.example`. `DB_CONNECTION=mysql` harus tetap ada karena bawaan config = `sqlite`. Jangan commit `.env` (sudah ada di `.gitignore`).
- Database lokal: MySQL 8 di container Docker `mysql_server` (port 3306, database `lms_pjj`), bukan MySQL XAMPP. Container harus menyala agar aplikasi jalan.
- `setup.sh`/`setup.bat` menjalankan `migrate:fresh --seed`: menghapus seluruh data. Skrip ini tidak menjalankan npm (aset sudah di `public/build`).
- `npm run dev` membuat `public/hot`; selama file itu ada, halaman memuat aset dari server Vite. Matikan dev server sebelum menguji hasil build.
- Tidak ada `.htaccess` di root proyek, sehingga mode `http://localhost/lms-smk2/...` lewat Apache belum melayani route & file statis (`/build/...`) dengan benar. Gunakan `php artisan serve`, atau arahkan document root ke `public/`.
