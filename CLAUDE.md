# CLAUDE.md

Panduan untuk Claude Code saat bekerja di repo ini.

## Ringkasan

LMS Pembelajaran Jarak Jauh (PJJ) untuk sekolah SD/SMP/SMA/SMK. Laravel 12, PHP ^8.2, MySQL/MariaDB, Blade murni. Tiga peran: `admin`, `guru`, `siswa`. Seluruh UI, kode, nama tabel/kolom/route/method memakai **Bahasa Indonesia**: ikuti konvensi ini (`simpan`, `hapus`, `kumpul`, `tenggat`, `terbit`, ...).

Dijalankan di XAMPP macOS: `/Applications/XAMPP/xamppfiles/htdocs/lms-smk2`, diakses lewat `http://localhost/lms-smk2` (root `index.php` meneruskan ke `public/index.php`) atau `php artisan serve`.

## Perintah

```bash
composer install
php artisan migrate:fresh --seed   # reset DB + data contoh (merusak data!)
php artisan serve
php artisan test                   # tests/ baru berisi contoh bawaan Laravel
vendor/bin/pint                    # format kode
php artisan optimize:clear         # bersihkan cache, termasuk cache 'pengaturan'
```

Akun demo (seeder): `admin/admin123`, `guru/guru123`, `siswa/siswa123`; akun lain password `rahasia123`.

## Tanpa Node/NPM

Tidak ada Vite/build step. Semua CSS di `public/css/app.css`, JS inline di Blade, ikon SVG Lucide inline lewat `icon('nama')` di `app/helpers.php` (tambahkan path baru ke array di sana). Jangan menambahkan `@vite`, npm, atau framework CSS. Skrip `composer setup`/`composer dev` memanggil npm: sisa template, jangan dipakai.

## Struktur modular

Kode aplikasi ada di `app/Modules/<Modul>/` (Auth, Dashboard, Sekolah, Jurusan, Mapel, Kelas, Pengguna, Ruang, Pertemuan, Tugas, Kuis, Diskusi, Pengumuman, Rekap). Tiap modul:

```
app/Modules/Kuis/
  Controllers/KuisController.php   namespace App\Modules\Kuis\Controllers, extends App\Http\Controllers\Controller
  Models/Kuis.php, Soal.php, ...   namespace App\Modules\Kuis\Models
  Views/form.blade.php, ...        view('kuis::form'), @include('kuis::...')
  routes.php                       route modul ini
```

- **Jangan** membuat file di `app/Models`, `app/Http/Controllers` (selain base `Controller.php`), atau `resources/views/<fitur>`: folder lama itu sudah tidak dipakai.
- Model ditaruh di modul pemiliknya (lihat tabel di README). Modul lain boleh memakainya lewat `use App\Modules\<Pemilik>\Models\X;`. Contoh: `User` ada di `Pengguna`, `Ruang` dan `Jadwal` ada di `Ruang`, `TahunAjaran` dan `Pengaturan` ada di `Sekolah`.
- Namespace view = nama folder modul huruf kecil (`ModuleServiceProvider`). View bersama tetap global: `layouts.app`, `partials.flash|kosong|hapus|paginasi`, `errors.*`. Header ruang ada di `ruang::_kepala`.
- `routes/web.php` hanya me-`require` semua `app/Modules/*/routes.php` (urutan abjad). Nama route global, jadi tetap unik lintas modul (`kuis.edit`, `admin.jurusan`, ...).
- **Middleware route**: tulis semua middleware grup dalam **satu** array, misalnya `Route::middleware(['auth', 'role', 'can:akses,ruang'])->prefix('ruang/{ruang}')`. Memanggil `->middleware()` dua kali pada satu rantai *menimpa* middleware sebelumnya, sehingga `auth` bisa hilang. Di dalam closure grup, `->middleware()` bertingkat aman.
- Migration, seeder, dan factory tetap terpusat di `database/` (satu migration LMS saling ber-FK).

**Membuat modul baru** (`app/Modules/Contoh`):
1. Buat `Controllers/`, `Models/`, `Views/`, dan `routes.php` dengan namespace `App\Modules\Contoh\...`.
2. Di `routes.php`, bungkus route dengan `Route::middleware(['auth', 'role', ...])` (tambahkan `'can:akses,ruang'` + prefix `ruang/{ruang}` untuk fitur di dalam ruang, atau `'role:admin'` + `prefix('admin')->name('admin.')` untuk fitur admin).
3. Panggil view dengan `view('contoh::nama')`. View otomatis terdaftar, tanpa registrasi tambahan.
4. Jalankan `composer dump-autoload` dan `php artisan optimize:clear`, lalu cek dengan `php artisan route:list`.

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
6. Teks bebas dari pengguna dirender dengan `{!! teks($s) !!}` (meng-escape lalu mengubah URL, `**tebal**`, paragraf). Jangan `{!! !!}` untuk input mentah.
7. Login dibatasi `throttle:10,1`; akun demo di halaman login disembunyikan saat `APP_ENV=production`.

## Konvensi kode

- Nama tabel singular Bahasa Indonesia (`kelas`, `ruang`, `tugas`, `kuis`, `pengumuman`, ...), jadi setiap model mendefinisikan `protected $table`.
- Skema LMS ada di satu migration `database/migrations/2026_10_27_000001_create_lms_tables.php`. Untuk perubahan skema, buat migration baru (jangan ubah yang lama bila sudah ada data nyata) lalu perbarui `DatabaseSeeder` bila perlu.
- Seeder deterministik (`mt_srand(2026)`) dan tanggalnya relatif terhadap `now()`: pertahankan agar demo selalu terlihat "hidup".
- Fitur admin ada di modulnya masing-masing (mis. `Jurusan`, `Rekap`, `Ruang\Controllers\PembagianController`), route admin berprefiks URL `admin/` dan nama `admin.`. Validasi inline dengan `$r->validate()` (belum memakai FormRequest), pesan error Bahasa Indonesia.
- Layout `layouts/app.blade.php`; paginator memakai `partials.paginasi`.
- Helper global (`app/helpers.php`, autoload via composer `files`): `setting`, `tgl`, `tgl_panjang`, `sisa_waktu`, `teks`, `predikat`, `icon`. Format tanggal Indonesia via `tgl()`, jangan format manual.
- Timezone `Asia/Jakarta`, locale `id`. Menurut `.env.example`, session & cache memakai driver `file` dan queue `database`.
- Gaya kode ringkas (variabel pendek seperti `$r`, `$u`, `$p`), ikuti gaya file yang sedang diedit.

## Git & commit

- Jangan menambahkan AI sebagai kontributor di commit: **tanpa** baris `Co-Authored-By: Claude ...` dan tanpa atribusi AI lain (misalnya "Generated with Claude Code") di pesan commit maupun deskripsi PR.
- Commit memakai identitas git milik user (`git config user.name` / `user.email`); jangan mengubah author atau konfigurasi git.
- Commit hanya bila diminta user.

## Catatan lingkungan / gotcha

- `.env` dibuat dari `.env.example`. `DB_CONNECTION=mysql` harus tetap ada karena bawaan config = `sqlite`. Jangan commit `.env` (sudah ada di `.gitignore`).
- Database lokal: MySQL 8 di container Docker `mysql_server` (port 3306, database `lms_pjj`), bukan MySQL XAMPP. Container harus menyala agar aplikasi jalan.
- `setup.sh`/`setup.bat` menjalankan `migrate:fresh --seed`: menghapus seluruh data.
