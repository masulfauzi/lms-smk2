# LMS PJJ Sekolah — Pembelajaran Jarak Jauh SD, SMP, SMA, SMK (Laravel 12)

Learning Management System untuk pembelajaran jarak jauh (PJJ) di sekolah, dibangun dengan Laravel 12 dan MySQL. Satu aplikasi bisa dipakai untuk jenjang SD, SMP, SMA, maupun SMK: cukup pilih jenjang di pengaturan, maka pilihan tingkat kelas menyesuaikan (SD 1–6, SMP 7–9, SMA/SMK 10–12) dan menu jurusan muncul untuk SMA/SMK.

Ada tiga peran: **admin sekolah**, **guru**, dan **siswa**. Login memakai NIP (guru) atau NISN (siswa).

## Cara kerja

Admin mengisi data kelas, mata pelajaran, guru, dan siswa (bisa impor dari CSV/Excel), lalu membagi tugas mengajar: setiap mapel di satu kelas menjadi satu **ruang belajar** yang diampu seorang guru, lengkap dengan jadwal dan link kelas daring (Google Meet/Zoom).

Di ruang belajar, guru membuat **pertemuan** berisi materi teks, video YouTube, dan lampiran (PDF, PowerPoint, Word, dll). Setiap pertemuan bisa membuka **presensi daring** pada jam tertentu: siswa menekan tombol *Saya hadir*, siswa yang tidak presensi sampai jam tutup otomatis tercatat alpa, dan guru bisa mengubah status menjadi izin/sakit.

Guru memberi **tugas** dengan tenggat; siswa mengumpulkan file atau jawaban teks, pengumpulan terlambat ditandai otomatis, dan guru menilai satu per satu lengkap dengan komentar. **Kuis/ulangan online** mendukung soal pilihan ganda (dinilai otomatis) dan esai (dikoreksi guru), acak soal dan opsi, batas waktu, jawaban tersimpan otomatis, dan pengumpulan otomatis saat waktu habis.

Nilai akhir tiap mapel dihitung dari rata-rata tugas, kuis, dan persentase kehadiran sesuai bobot yang diatur guru, lalu dibandingkan dengan KKM. Admin melihat leger nilai satu kelas, rekap presensi bulanan, dan pantauan keaktifan siswa untuk menindaklanjuti siswa yang tidak mengikuti PJJ.

## Fitur

**Siswa**
- Beranda: presensi yang sedang dibuka, tugas yang belum dikumpulkan beserta sisa waktu, kuis aktif, jadwal hari ini, pengumuman
- Progres belajar per mapel (materi yang sudah ditandai selesai)
- Materi per pertemuan: teks, video YouTube, lampiran yang bisa dibuka/diunduh
- Presensi daring satu klik selama jam presensi dibuka
- Kumpulkan tugas (file/teks), lihat nilai dan komentar guru
- Kerjakan kuis berwaktu, navigasi nomor soal, simpan otomatis, tetap aman bila sinyal sempat putus
- Nilai sementara per mapel, diskusi tanya jawab dengan guru

**Guru**
- Beranda: tugas menunggu penilaian, kuis aktif, jadwal mengajar hari ini
- Kelola pertemuan (draf/terbit), jam presensi, tugas dan lampiran soal
- Penilaian tugas cepat: daftar siswa di samping, simpan nilai lalu lanjut ke siswa berikutnya
- Kuis: pilihan ganda & esai, bobot per soal, impor banyak soal sekaligus dari teks (salin dari Word), koreksi esai, reset percobaan siswa
- Analisis butir soal: persentase jawaban benar dan sebaran pilihan tiap soal
- Rekap presensi (matriks siswa x pertemuan), rekap nilai lengkap + unduh CSV
- Atur KKM, bobot nilai (tugas/kuis/kehadiran), link kelas daring per jadwal
- Pengumuman untuk kelas yang diajar

**Admin sekolah**
- Dashboard: jumlah siswa aktif hari ini, kehadiran PJJ per kelas hari ini, ruang belajar yang belum mengunggah materi seminggu terakhir
- Data sekolah, jenjang, tahun ajaran & semester (salin pembagian mengajar ke semester baru)
- Jurusan, kelas & wali kelas, mata pelajaran (Umum, Kejuruan, Muatan Lokal)
- Data guru & siswa: tambah, ubah, nonaktifkan, reset password, impor CSV
- Pembagian mengajar & jadwal mingguan dengan pencegahan jadwal bentrok
- Rekap presensi bulanan per kelas, leger nilai semua mapel (CSV/cetak), pantauan keaktifan siswa
- Pengumuman untuk semua, guru saja, siswa saja, atau kelas tertentu (bisa disematkan)

**Teknis**
- Laravel 12 standar: controller, model Eloquent, migration, seeder, Blade (tanpa Node.js/NPM)
- Hak akses per peran dan per ruang belajar; siswa hanya bisa membuka kelasnya sendiri
- Berkas materi/tugas disimpan di storage privat, hanya bisa diunduh oleh yang berhak
- Validasi jenis & ukuran file, perlindungan CSRF, batas percobaan login
- Tampilan responsif, nyaman dibuka di HP siswa
- Data contoh: SMK dengan 5 kelas, 10 guru, 120 siswa, materi, presensi, tugas, dan kuis beberapa minggu

## Instalasi

**Kebutuhan:** PHP 8.2 atau lebih baru (XAMPP 8.2+ / Laragon), MySQL/MariaDB, dan [Composer](https://getcomposer.org).

1. Letakkan folder proyek di web root, misalnya:
   - Windows: `C:\xampp\htdocs\lms-smk2` (atau `C:\laragon\www\lms-smk2`)
   - macOS: `/Applications/XAMPP/xamppfiles/htdocs/lms-smk2`
2. Jalankan Apache dan MySQL, buka `http://localhost/phpmyadmin`, buat database `lms_pjj`.
3. Skrip setup otomatis menyalin `.env.example` ke `.env` bila `.env` belum ada. Sesuaikan bila perlu:
   - `APP_URL` sesuai nama folder, misalnya `http://localhost/lms-smk2`
   - `DB_HOST`, `DB_PORT`, `DB_USERNAME`, `DB_PASSWORD` sesuai server MySQL Anda
   - Biarkan `DB_CONNECTION=mysql` (bawaan konfigurasi Laravel adalah `sqlite`)
4. Jalankan setup: Windows klik dua kali `setup.bat`, Linux/macOS `bash setup.sh`. Skrip ini menjalankan `composer install`, `key:generate`, `migrate:fresh --seed` (**menghapus seluruh isi database**), dan `storage:link`.
5. Buka `http://localhost/lms-smk2` dan login (file `index.php` di root meneruskan ke `public/`, jadi tidak perlu mengetik `/public`):
   - Admin: `admin` / `admin123`
   - Guru: `guru` / `guru123`
   - Siswa: `siswa` / `siswa123`
   - Guru & siswa lain di data contoh: NIP/NISN masing-masing, password `rahasia123`

**Tanpa XAMPP:** `bash setup.sh` lalu `php artisan serve`, buka `http://127.0.0.1:8000`.

**Untuk hosting:** arahkan document root ke folder `public/`, ubah `APP_ENV=production`, `APP_DEBUG=false`, dan `APP_URL` di `.env`. Akun demo di halaman login otomatis disembunyikan saat `APP_ENV=production`; ganti password admin setelah instalasi.

## Mengganti ke jenjang SD / SMP / SMA

Login sebagai admin → **Pengaturan** → ubah nama sekolah dan jenjang. Lalu sesuaikan data kelas, mapel, guru, dan siswa (hapus data contoh atau jalankan `php artisan migrate:fresh --force` untuk mulai dari database kosong, kemudian buat akun admin lewat `php artisan tinker`).

## Konsep data

```
Tahun ajaran (aktif: satu) ─┐
Kelas (tingkat, jurusan, wali) ─┼─► Ruang belajar (1 mapel × 1 kelas × 1 tahun ajaran, diampu 1 guru)
Mata pelajaran ─┘                     ├─ Jadwal (hari, jam, link meet)
                                      ├─ Pertemuan (materi + presensi daring) ─ Presensi, Materi selesai
                                      ├─ Tugas ─ Pengumpulan (nilai, komentar)
                                      ├─ Kuis ─ Soal (PG/esai), Percobaan (jawaban, nilai)
                                      └─ Diskusi
```

Siswa terhubung ke ruang lewat `kelas_id`; guru lewat `ruang.guru_id`. Pengumuman bisa untuk semua, guru, siswa, atau satu kelas.

**Nilai akhir** (lihat `app/Support/Rapor.php`) = rata-rata berbobot dari:
- rata-rata tugas (tugas tidak dikumpulkan setelah tenggat = 0; yang belum dinilai belum dihitung),
- rata-rata kuis (kuis ditutup tanpa dikerjakan = 0),
- persentase kehadiran = hadir ÷ (pertemuan yang presensinya sudah tutup − izin − sakit).

Bobot (bawaan 40/40/20) dan KKM diatur per ruang belajar; bila salah satu komponen belum punya data, bobotnya dinormalkan ulang. Predikat: A ≥ 90, B ≥ 80, C ≥ 70, D < 70.

## Struktur proyek

Aplikasi dibagi menjadi modul. Setiap modul punya folder sendiri berisi controller, model, view, dan route:

```
app/Modules/<Modul>/
  Controllers/    App\Modules\<Modul>\Controllers\...
  Models/         App\Modules\<Modul>\Models\...
  Views/          dipanggil dengan view('<modul>::nama')
  routes.php      route milik modul ini
```

| Modul | Isi |
|---|---|
| `Auth` | login, logout, profil & ganti password |
| `Dashboard` | beranda per peran, jadwal mingguan |
| `Sekolah` | data sekolah, jenjang, tahun ajaran (model `Pengaturan`, `TahunAjaran`) |
| `Jurusan`, `Mapel`, `Kelas` | data master |
| `Pengguna` | data guru & siswa, impor CSV, reset password (model `User`) |
| `Ruang` | ruang belajar, anggota, rekap presensi & nilai per ruang, pengaturan; pembagian mengajar & jadwal (model `Ruang`, `Jadwal`) |
| `Pertemuan` | materi, presensi daring, unduh lampiran materi (model `Pertemuan`, `Presensi`) |
| `Tugas` | tugas, pengumpulan, penilaian, unduh berkas (model `Tugas`, `Pengumpulan`) |
| `Kuis` | kuis, soal, percobaan, koreksi esai (model `Kuis`, `Soal`, `Percobaan`) |
| `Diskusi` | tanya jawab per ruang |
| `Pengumuman` | pengumuman sekolah/kelas |
| `Rekap` | rekap presensi bulanan, leger nilai, keaktifan siswa (admin) |

Bagian bersama di luar modul:

```
app/Http/Controllers/Controller.php   base controller: milik(), kirimBerkas()
app/Http/Middleware/Role.php          cek akun aktif + peran, catat waktu terakhir aktif
app/Providers/ModuleServiceProvider   mendaftarkan view tiap modul
app/Support/Rapor.php                 perhitungan nilai & kehadiran per ruang
app/helpers.php                       setting(), tgl(), sisa_waktu(), teks(), predikat(), icon()
routes/web.php                        memuat semua app/Modules/*/routes.php
resources/views/                      layouts/, partials/, errors/ (view bersama)
database/migrations/                  tabel bawaan Laravel + 2026_10_27_0000NN_*.php (satu file per tabel LMS, urut sesuai FK)
database/seeders/                     DatabaseSeeder: data contoh SMK
public/css/app.css                    seluruh gaya tampilan (tanpa build tool)
index.php                             penerus ke public/ agar bisa dibuka di http://localhost/<folder>
```

## Pengembangan

```bash
composer install                       # termasuk dev dependency (phpunit, pint)
php artisan migrate:fresh --seed       # reset database + data contoh
php artisan serve                      # http://127.0.0.1:8000
php artisan test                       # jalankan tes
vendor/bin/pint                        # rapikan gaya kode
php artisan optimize:clear             # bersihkan cache (termasuk cache pengaturan sekolah)
```

Tidak memerlukan Node.js/NPM. Skrip `composer setup` dan `composer dev` di `composer.json` adalah sisa template Laravel dan memanggil `npm`; gunakan `setup.sh`/`setup.bat` atau perintah di atas.
