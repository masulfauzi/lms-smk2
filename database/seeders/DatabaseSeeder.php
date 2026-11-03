<?php

namespace Database\Seeders;

use App\Models\Diskusi;
use App\Models\Jadwal;
use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\Kuis;
use App\Models\Mapel;
use App\Models\Pengaturan;
use App\Models\Pengumpulan;
use App\Models\Pengumuman;
use App\Models\Percobaan;
use App\Models\Pertemuan;
use App\Models\Presensi;
use App\Models\Ruang;
use App\Models\TahunAjaran;
use App\Models\Tugas;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Data contoh SMK: jurusan, kelas, mapel, guru, siswa, pembagian mengajar, jadwal,
 * dan isi pembelajaran (materi, presensi, tugas, kuis, diskusi) beberapa minggu terakhir.
 * Tanggal dibuat relatif terhadap hari ini supaya demo selalu terlihat "hidup".
 */
class DatabaseSeeder extends Seeder
{
    private string $hash;

    public function run(): void
    {
        mt_srand(2026);
        $this->hash = Hash::make('rahasia123');

        Pengaturan::simpan(Pengaturan::BAWAAN);
        $ganjil = now()->month >= 7;
        $ta = TahunAjaran::create([
            'nama' => $ganjil ? now()->year . '/' . (now()->year + 1) : (now()->year - 1) . '/' . now()->year,
            'semester' => $ganjil ? 'Ganjil' : 'Genap', 'aktif' => true,
        ]);

        // ---------- Pengguna & data master ----------
        User::create(['name' => 'Admin Sekolah', 'username' => 'admin', 'role' => 'admin', 'password' => 'admin123', 'aktif' => true, 'terakhir_aktif_at' => now()]);

        $jurusan = [];
        foreach ([['TKJ', 'Teknik Komputer dan Jaringan'], ['AKL', 'Akuntansi dan Keuangan Lembaga'], ['BD', 'Bisnis Digital']] as [$kode, $nama]) {
            $jurusan[$kode] = Jurusan::create(['kode' => $kode, 'nama' => $nama]);
        }

        $mapel = [];
        foreach ([
            ['PAI', 'Pendidikan Agama Islam', 'Umum'], ['PPKN', 'Pendidikan Pancasila', 'Umum'],
            ['BIN', 'Bahasa Indonesia', 'Umum'], ['MTK', 'Matematika', 'Umum'], ['BING', 'Bahasa Inggris', 'Umum'],
            ['INF', 'Informatika', 'Umum'], ['SEJ', 'Sejarah', 'Umum'], ['PJOK', 'Pendidikan Jasmani', 'Umum'],
            ['DTKJ', 'Dasar-dasar Teknik Jaringan', 'Kejuruan'], ['AKD', 'Akuntansi Dasar', 'Kejuruan'],
            ['BJW', 'Bahasa Jawa', 'Muatan Lokal'],
        ] as [$kode, $nama, $kelompok]) {
            $mapel[$kode] = Mapel::create(['kode' => $kode, 'nama' => $nama, 'kelompok' => $kelompok]);
        }

        // Guru: [NIP/username, nama, jk, mapel]. Akun demo "guru" mengajar Matematika.
        $guruData = [
            ['guru', 'Rina Kartikasari, S.Pd.', 'P', 'MTK'],
            ['198203112008012004', 'Drs. Hadi Purnomo', 'L', 'PAI'],
            ['198507242010012011', 'Sulastri, S.Pd.', 'P', 'BIN'],
            ['199001052015031002', 'Yoga Pratama, S.Kom.', 'L', 'INF'],
            ['198811172014022001', 'Dewi Anggraeni, S.Pd.', 'P', 'BING'],
            ['197905302005011006', 'Bambang Setiawan, S.Pd.', 'L', 'SEJ'],
            ['199206142019031008', 'Arif Nugroho, S.Or.', 'L', 'PJOK'],
            ['198709092011012013', 'Fitri Handayani, S.E.', 'P', 'AKD'],
            ['199304212020122005', 'Nanda Puspita, S.Pd.', 'P', 'MTK'],
            ['198612012009031009', 'Teguh Wibowo, S.T.', 'L', 'DTKJ'],
        ];
        $guru = [];
        foreach ($guruData as [$nip, $nama, $jk]) {
            $guru[$nip] = User::create(['name' => $nama, 'username' => $nip, 'jk' => $jk, 'role' => 'guru',
                'password' => $nip === 'guru' ? 'guru123' : $this->hash, 'aktif' => true, 'terakhir_aktif_at' => now()->subHours(mt_rand(1, 40))]);
        }
        // Pengampu per mapel (mapel tanpa guru khusus dirangkap guru serumpun)
        $pengampu = ['PAI' => '198203112008012004', 'PPKN' => '197905302005011006', 'BIN' => '198507242010012011', 'BJW' => '198507242010012011',
            'BING' => '198811172014022001', 'INF' => '199001052015031002', 'SEJ' => '197905302005011006', 'PJOK' => '199206142019031008',
            'DTKJ' => '198612012009031009', 'AKD' => '198709092011012013'];

        $kelasData = [['X TKJ 1', 10, 'TKJ', '199001052015031002'], ['X AKL 1', 10, 'AKL', '198709092011012013'],
            ['XI TKJ 1', 11, 'TKJ', '198612012009031009'], ['XI BD 1', 11, 'BD', '198811172014022001'], ['XII TKJ 1', 12, 'TKJ', 'guru']];
        $kelas = [];
        $siswaKelas = [];
        $nisn = 81230000;
        foreach ($kelasData as [$nama, $tingkat, $jur, $wali]) {
            $k = Kelas::create(['nama' => $nama, 'tingkat' => $tingkat, 'jurusan_id' => $jurusan[$jur]->id, 'wali_id' => $guru[$wali]->id]);
            $kelas[$nama] = $k;
            // 24 siswa per kelas. Akun demo "siswa" ada di X TKJ 1.
            $daftar = [];
            for ($i = 0; $i < 24; $i++) {
                $jk = $i % 2 ? 'P' : 'L';
                $demo = $nama === 'X TKJ 1' && $i === 0;
                $daftar[] = User::create([
                    'name' => $demo ? 'Raka Aditya Pratama' : $this->namaAcak($jk),
                    'username' => $demo ? 'siswa' : '00' . ($nisn++), 'jk' => $jk, 'role' => 'siswa', 'kelas_id' => $k->id,
                    'password' => $demo ? 'siswa123' : $this->hash, 'aktif' => true,
                    'terakhir_aktif_at' => $demo ? now() : (mt_rand(1, 100) <= 7 ? null : now()->subMinutes(mt_rand(1, 100) <= 15 ? mt_rand(4400, 12000) : mt_rand(5, 1500))),
                ]);
            }
            $siswaKelas[$k->id] = collect($daftar)->sortBy('name')->values();
        }

        // ---------- Pembagian mengajar + jadwal mingguan ----------
        $semuaRuang = [];
        foreach ($kelasData as [$nama, , $jur]) {
            $k = $kelas[$nama];
            $daftarMapel = array_merge(['PAI', 'PPKN', 'BIN', 'MTK', 'BING', 'INF', 'SEJ', 'PJOK', 'BJW'], $jur === 'TKJ' ? ['DTKJ'] : ($jur === 'AKL' ? ['AKD'] : []));
            foreach ($daftarMapel as $slot => $kode) {
                $gid = $kode === 'MTK' ? (in_array($nama, ['X TKJ 1', 'X AKL 1', 'XI TKJ 1']) ? 'guru' : '199304212020122005') : $pengampu[$kode];
                $r = Ruang::create(['tahun_ajaran_id' => $ta->id, 'kelas_id' => $k->id, 'mapel_id' => $mapel[$kode]->id, 'guru_id' => $guru[$gid]->id, 'kkm' => 75]);
                // Senin-Jumat, dua blok pagi per hari; mapel ke-11 di blok siang Senin
                $hari = $slot < 10 ? intdiv($slot, 2) + 1 : 1;
                [$mulai, $selesai] = $slot < 10 ? [['07:30', '09:00'], ['10:00', '11:30']][$slot % 2] : ['12:45', '14:15'];
                Jadwal::create(['ruang_id' => $r->id, 'hari' => $hari, 'jam_mulai' => $mulai, 'jam_selesai' => $selesai,
                    'link_meet' => 'https://meet.google.com/' . substr(md5("a$r->id"), 0, 3) . '-' . substr(md5("b$r->id"), 0, 4) . '-' . substr(md5("c$r->id"), 0, 3)]);
                $semuaRuang[] = $r;
            }
        }

        // ---------- Ruang demo: Matematika X TKJ 1 ----------
        $mtk = Ruang::where('kelas_id', $kelas['X TKJ 1']->id)->where('mapel_id', $mapel['MTK']->id)->first();
        $mtk->update(['deskripsi' => "Selamat datang di kelas Matematika X TKJ 1.\n\nSetiap pertemuan: baca materi, tekan **Saya hadir** saat presensi dibuka, lalu kerjakan latihan. Tugas dikumpulkan paling lambat pukul 23.59 di hari tenggat.\n\nBuku pegangan: Matematika SMK Kelas X (Kurikulum Merdeka)."]);
        $this->isiMatematika($mtk, $siswaKelas[$mtk->kelas_id], $guru['guru']);

        // ---------- Ruang lain: pertemuan ringan + presensi ----------
        $topik = [
            'PAI' => ['Iman kepada Allah SWT', 'Asmaul Husna', 'Perilaku jujur dalam kehidupan', 'Adab menuntut ilmu'],
            'PPKN' => ['Nilai-nilai Pancasila', 'Hak dan kewajiban warga negara', 'Bhinneka Tunggal Ika'],
            'BIN' => ['Teks laporan hasil observasi', 'Teks eksposisi', 'Menulis surat lamaran kerja', 'Teks prosedur'],
            'BING' => ['Self introduction', 'Describing people', 'Asking and giving opinion', 'Job interview'],
            'INF' => ['Berpikir komputasional', 'Algoritma dan pemrograman dasar', 'Keamanan data pribadi'],
            'SEJ' => ['Konsep dasar sejarah', 'Kerajaan Hindu-Buddha di Indonesia', 'Masuknya Islam ke Nusantara'],
            'PJOK' => ['Kebugaran jasmani di rumah', 'Teknik dasar bola voli', 'Pola hidup sehat'],
            'DTKJ' => ['Pengenalan jaringan komputer', 'Topologi jaringan', 'Pengkabelan UTP', 'Pengalamatan IP'],
            'AKD' => ['Persamaan dasar akuntansi', 'Jurnal umum', 'Buku besar'],
            'BJW' => ['Unggah-ungguh basa', 'Tembang macapat', 'Aksara Jawa'],
            'MTK' => ['Eksponen dan logaritma', 'Barisan dan deret', 'Persamaan kuadrat'],
        ];
        foreach ($semuaRuang as $r) {
            if ($r->id === $mtk->id) continue;
            $kode = $r->mapel->kode;
            $sepi = $r->id % 7 === 0; // sebagian sengaja belum isi materi minggu ini (contoh pantauan admin)
            $jumlah = $sepi ? 1 : mt_rand(2, count($topik[$kode]));
            foreach (array_slice($topik[$kode], 0, $jumlah) as $i => $judul) {
                $mundur = $sepi ? 15 : ($jumlah - 1 - $i) * 7 + ($r->id % 3 === 0 ? 0 : mt_rand(1, 3));
                $tgl = today()->subDays($mundur);
                $p = Pertemuan::create([
                    'ruang_id' => $r->id, 'urutan' => $i + 1, 'judul' => $judul, 'tanggal' => $tgl, 'terbit' => true,
                    'isi' => "Pada pertemuan ini kita mempelajari **$judul**.\n\nBacalah ringkasan materi berikut dengan saksama, lalu catat hal penting di buku tulis masing-masing. Jika ada yang belum jelas, tanyakan di tab Diskusi.\n\nTujuan pembelajaran:\n1. Memahami konsep $judul.\n2. Menerapkan konsep tersebut dalam contoh sehari-hari.\n3. Menyelesaikan latihan yang diberikan guru.",
                    'presensi_buka' => $tgl->copy()->setTime($mundur === 0 ? 0 : 7, 0), 'presensi_tutup' => $tgl->copy()->setTime($mundur === 0 ? 23 : 15, $mundur === 0 ? 59 : 0),
                ]);
                $this->isiPresensi($p, $siswaKelas[$r->kelas_id], mt_rand(78, 94));
                foreach ($siswaKelas[$r->kelas_id] as $s) {
                    if (mt_rand(1, 100) <= ($s->username === 'siswa' ? 75 : 60)) {
                        DB::table('materi_selesai')->insert(['pertemuan_id' => $p->id, 'siswa_id' => $s->id, 'selesai_at' => $tgl->copy()->setTime(mt_rand(8, 20), mt_rand(0, 59))]);
                    }
                }
            }
        }

        // ---------- Pengumuman ----------
        $admin = User::where('username', 'admin')->first();
        $umum = [
            [$admin, 'Jadwal Penilaian Tengah Semester (PTS) daring', 'semua', null, true, 2,
                "PTS semester ini dilaksanakan secara daring melalui menu Kuis di masing-masing ruang belajar.\n\nPastikan kuota internet cukup dan perangkat sudah dicoba sebelum hari pelaksanaan. Jadwal lengkap dibagikan oleh wali kelas."],
            [$admin, 'Rapat koordinasi guru PJJ', 'guru', null, false, 4,
                'Rapat evaluasi pelaksanaan PJJ hari Jumat pukul 13.30 melalui Google Meet. Mohon setiap guru membawa rekap kehadiran siswa.'],
            [$guru['guru'], 'Kuis SPLDV dibuka hari ini', 'siswa', $kelas['X TKJ 1']->id, false, 0,
                'Anak-anak, kuis Sistem Persamaan Linear Dua Variabel sudah dibuka di ruang Matematika. Waktu mengerjakan 30 menit. Selamat mengerjakan!'],
            [$admin, 'Libur memperingati hari besar nasional', 'semua', null, false, 9,
                'Kegiatan belajar daring diliburkan pada tanggal merah. Tugas yang tenggatnya jatuh pada hari libur berlaku sampai hari berikutnya.'],
        ];
        foreach ($umum as [$u, $judul, $sasaran, $kelasId, $pin, $hari, $isi]) {
            $p = Pengumuman::create(['user_id' => $u->id, 'judul' => $judul, 'sasaran' => $sasaran, 'kelas_id' => $kelasId, 'disematkan' => $pin, 'isi' => $isi]);
            $p->forceFill(['created_at' => now()->subDays($hari)->subHours($hari ? 0 : 3)])->save();
        }
    }

    /** Ruang demo: 6 pertemuan SPLDV + 3 tugas + 2 kuis + diskusi */
    private function isiMatematika(Ruang $r, $siswa, User $guru): void
    {
        $materi = [
            ['Pengertian Persamaan Linear Dua Variabel', "Persamaan linear dua variabel (PLDV) adalah persamaan yang memiliki dua variabel dengan pangkat tertinggi satu. Bentuk umumnya:\n\n**ax + by = c**, dengan a dan b tidak sama dengan nol.\n\nContoh: 2x + 3y = 12. Pasangan (3, 2) adalah salah satu penyelesaiannya karena 2(3) + 3(2) = 12.\n\nSatu PLDV memiliki tak hingga banyak penyelesaian. Jika digambar pada bidang Kartesius, himpunan penyelesaiannya membentuk garis lurus."],
            ['Sistem Persamaan Linear Dua Variabel (SPLDV)', "SPLDV adalah dua buah PLDV yang saling berkaitan dan memiliki satu penyelesaian yang sama.\n\nContoh:\nx + y = 7\nx - y = 1\n\nPenyelesaiannya adalah x = 4 dan y = 3, karena pasangan ini memenuhi kedua persamaan sekaligus.\n\nAda tiga kemungkinan banyaknya penyelesaian SPLDV: tepat satu (garis berpotongan), tidak ada (garis sejajar), atau tak hingga (garis berimpit)."],
            ['Metode Grafik', "Langkah menyelesaikan SPLDV dengan metode grafik:\n\n1. Tentukan titik potong tiap persamaan dengan sumbu X (y = 0) dan sumbu Y (x = 0).\n2. Gambar kedua garis pada bidang Kartesius yang sama.\n3. Titik potong kedua garis adalah penyelesaiannya.\n\nMetode ini cocok untuk memahami konsep, tetapi kurang teliti bila penyelesaiannya pecahan."],
            ['Metode Substitusi', "Langkah metode substitusi:\n\n1. Ubah salah satu persamaan menjadi bentuk x = ... atau y = ...\n2. Substitusikan ke persamaan yang lain sehingga tersisa satu variabel.\n3. Selesaikan, lalu substitusikan kembali untuk mencari variabel kedua.\n\nContoh: x + y = 10 dan 2x - y = 5. Dari persamaan pertama, y = 10 - x. Substitusi: 2x - (10 - x) = 5, sehingga 3x = 15 dan x = 5. Maka y = 5."],
            ['Metode Eliminasi dan Gabungan', "Metode eliminasi menghilangkan salah satu variabel dengan menjumlahkan atau mengurangkan kedua persamaan setelah koefisiennya disamakan.\n\nMetode gabungan (eliminasi-substitusi) paling sering dipakai: eliminasi untuk mencari variabel pertama, lalu substitusi untuk variabel kedua.\n\nContoh: 3x + 2y = 16 dan x + 2y = 8. Kurangkan: 2x = 8, x = 4. Substitusi ke x + 2y = 8: 2y = 4, y = 2."],
            ['Penerapan SPLDV dalam Kehidupan Sehari-hari', "Banyak masalah sehari-hari bisa dimodelkan dengan SPLDV, misalnya harga barang, umur, dan keuangan usaha.\n\nContoh: harga 2 kabel UTP dan 3 konektor RJ45 adalah Rp41.000, sedangkan 1 kabel dan 5 konektor Rp34.500. Misalkan kabel = x dan konektor = y, maka:\n2x + 3y = 41.000\nx + 5y = 34.500\n\nSelesaikan dengan metode gabungan untuk mendapatkan harga masing-masing.\n\nHari ini presensi dibuka sampai pukul 23.59, dan Kuis 2 juga sudah dibuka."],
        ];
        foreach ($materi as $i => [$judul, $isi]) {
            $tgl = today()->subDays((5 - $i) * 7);
            $hariIni = $i === 5;
            $p = Pertemuan::create([
                'ruang_id' => $r->id, 'urutan' => $i + 1, 'judul' => $judul, 'isi' => $isi, 'tanggal' => $tgl, 'terbit' => true,
                'presensi_buka' => $tgl->copy()->setTime($hariIni ? 0 : 6, 0), 'presensi_tutup' => $tgl->copy()->setTime($hariIni ? 23 : 15, $hariIni ? 59 : 0),
            ]);
            if ($i === 0) {
                $path = "materi/ruang-{$r->id}/ringkasan-pldv.pdf";
                Storage::put($path, $this->pdfSederhana(['Ringkasan Materi: Persamaan Linear Dua Variabel', '', 'Bentuk umum: ax + by = c', 'Contoh: 2x + 3y = 12, salah satu penyelesaiannya (3, 2).', 'Satu PLDV memiliki tak hingga banyak penyelesaian.', 'Grafiknya berupa garis lurus pada bidang Kartesius.']));
                $p->update(['lampiran_path' => $path, 'lampiran_nama' => 'Ringkasan PLDV.pdf']);
            }
            // Pertemuan hari ini masih berjalan: baru sebagian presensi, demo siswa belum
            $this->isiPresensi($p, $siswa, $hariIni ? 45 : 90, $hariIni ? ['siswa'] : []);
            foreach ($siswa as $s) {
                if ($s->username === 'siswa' && $i >= 4) continue;
                if ($s->username === 'siswa' || mt_rand(1, 100) <= ($hariIni ? 20 : 80)) {
                    DB::table('materi_selesai')->insert(['pertemuan_id' => $p->id, 'siswa_id' => $s->id, 'selesai_at' => $tgl->copy()->setTime(mt_rand(8, 20), mt_rand(0, 59))]);
                }
            }
        }

        // ---------- Tugas ----------
        $tugas = [
            ['Latihan 1: Menentukan penyelesaian PLDV', 22, 2, 'Kerjakan latihan halaman 34 nomor 1-5. Tulis langkah pengerjaan dengan jelas, foto hasil tulisan tangan, lalu unggah dalam satu file PDF atau JPG.', 'lalu'],
            ['Latihan 2: Metode substitusi', 8, 4, "Selesaikan 5 soal SPLDV berikut dengan metode substitusi:\n1. x + y = 9 dan x - y = 3\n2. 2x + y = 11 dan x - y = 1\n3. x + 2y = 8 dan 3x - y = 3\n4. 4x + y = 14 dan x + y = 5\n5. 2x - 3y = 1 dan x + y = 8", 'lalu'],
            ['Proyek mini: Soal cerita SPLDV dari lingkungan sekitar', -5, 6, "Buat 2 soal cerita SPLDV dari kehidupan sehari-hari (misal harga barang di toko komputer), lalu selesaikan dengan metode gabungan.\n\nBoleh dikumpulkan berupa teks atau file.", 'jalan'],
        ];
        foreach ($tugas as $n => [$judul, $hariLalu, $urutPertemuan, $instruksi, $status]) {
            $tenggat = today()->subDays($hariLalu)->setTime(23, 59);
            $t = Tugas::create(['ruang_id' => $r->id, 'pertemuan_id' => Pertemuan::where('ruang_id', $r->id)->where('urutan', $urutPertemuan)->value('id'),
                'judul' => $judul, 'instruksi' => $instruksi, 'tenggat' => $tenggat, 'jenis_kumpul' => $status === 'jalan' ? 'keduanya' : 'file', 'boleh_terlambat' => true]);
            foreach ($siswa as $s) {
                $demo = $s->username === 'siswa';
                if ($status === 'jalan') {
                    if (!$demo && mt_rand(1, 100) <= 35) {
                        Pengumpulan::create(['tugas_id' => $t->id, 'siswa_id' => $s->id, 'dikumpulkan_at' => now()->subHours(mt_rand(2, 60)), 'terlambat' => false,
                            'teks' => "Soal 1: Harga 3 mouse dan 2 keyboard Rp450.000, sedangkan 1 mouse dan 1 keyboard Rp175.000.\n3x + 2y = 450.000 dan x + y = 175.000\nDiperoleh mouse Rp100.000 dan keyboard Rp75.000."]);
                    }
                    continue;
                }
                if (!$demo && mt_rand(1, 100) <= 8) continue; // tidak mengumpulkan
                $terlambat = !$demo && mt_rand(1, 100) <= 10;
                $waktu = $terlambat ? $tenggat->copy()->addHours(mt_rand(2, 30)) : $tenggat->copy()->subHours(mt_rand(1, 96));
                $path = "tugas/{$t->id}/jawaban-{$s->id}.pdf";
                Storage::put($path, $this->pdfSederhana(['Jawaban ' . $judul, 'Nama: ' . $s->name, '', '1. x = 6, y = 3', '2. x = 4, y = 3', '3. x = 2, y = 3', '4. x = 3, y = 2', '5. x = 5, y = 3']));
                $dinilai = $n === 0 || $demo || mt_rand(1, 100) <= 70;
                Pengumpulan::create([
                    'tugas_id' => $t->id, 'siswa_id' => $s->id, 'file_path' => $path, 'file_nama' => 'jawaban-' . Str::slug($s->name) . '.pdf',
                    'dikumpulkan_at' => $waktu, 'terlambat' => $terlambat,
                    'nilai' => $dinilai ? ($demo ? [88, 92][$n] : mt_rand($terlambat ? 60 : 68, 98)) : null,
                    'komentar' => $dinilai && ($demo || mt_rand(1, 3) === 1) ? ['Langkah pengerjaan sudah runtut, pertahankan.', 'Bagus. Perhatikan penulisan tanda negatif.', 'Jawaban benar, tulisan lebih rapi lagi ya.'][mt_rand(0, 2)] : null,
                    'dinilai_at' => $dinilai ? $tenggat->copy()->addDays(2) : null,
                ]);
            }
        }

        // ---------- Kuis 1 (sudah selesai) ----------
        $k1 = Kuis::create(['ruang_id' => $r->id, 'judul' => 'Kuis 1: Konsep PLDV dan SPLDV', 'petunjuk' => 'Pilih satu jawaban yang paling tepat.',
            'mulai' => today()->subDays(13)->setTime(7, 0), 'selesai' => today()->subDays(13)->setTime(20, 0), 'durasi_menit' => 20, 'terbit' => true]);
        foreach ([
            ['Bentuk umum persamaan linear dua variabel adalah ...', ['A' => 'ax + by = c', 'B' => 'ax² + bx + c = 0', 'C' => 'y = mx²', 'D' => 'ax + b = 0'], 'A'],
            ['Pasangan berikut yang merupakan penyelesaian 2x + 3y = 12 adalah ...', ['A' => '(2, 3)', 'B' => '(3, 2)', 'C' => '(4, 2)', 'D' => '(1, 4)'], 'B'],
            ['Grafik himpunan penyelesaian PLDV berbentuk ...', ['A' => 'parabola', 'B' => 'lingkaran', 'C' => 'garis lurus', 'D' => 'titik'], 'C'],
            ['Jika x + y = 7 dan x - y = 1, nilai x adalah ...', ['A' => '3', 'B' => '4', 'C' => '5', 'D' => '6'], 'B'],
            ['SPLDV yang kedua garisnya sejajar memiliki ... penyelesaian.', ['A' => 'satu', 'B' => 'dua', 'C' => 'tak hingga', 'D' => 'tidak ada'], 'D'],
        ] as $i => [$tanya, $opsi, $kunci]) {
            $k1->soal()->create(['urutan' => $i + 1, 'jenis' => 'pg', 'pertanyaan' => $tanya, 'opsi' => $opsi, 'kunci' => $kunci, 'bobot' => 2]);
        }
        $k1->load('soal');
        foreach ($siswa as $s) {
            $demo = $s->username === 'siswa';
            if (!$demo && mt_rand(1, 100) <= 6) continue;
            $jawab = [];
            foreach ($k1->soal as $soal) {
                $benar = $demo ? $soal->urutan !== 5 : mt_rand(1, 100) <= ($soal->urutan === 5 ? 38 : 82);
                $salah = array_values(array_diff(array_keys($soal->opsi), [$soal->kunci]));
                $jawab[$soal->id] = $benar ? $soal->kunci : $salah[array_rand($salah)];
            }
            $mulai = $k1->mulai->copy()->addMinutes(mt_rand(30, 600));
            $p = Percobaan::create(['kuis_id' => $k1->id, 'siswa_id' => $s->id, 'mulai_at' => $mulai, 'batas_at' => $mulai->copy()->addMinutes(20),
                'selesai_at' => $mulai->copy()->addMinutes(mt_rand(7, 19)), 'urutan_soal' => ['soal' => $k1->soal->pluck('id')->shuffle()->all(), 'opsi' => []], 'jawaban' => $jawab]);
            $p->update(['nilai' => $p->hitungNilai()]);
        }

        // ---------- Kuis 2 (dibuka hari ini, ada esai) ----------
        $k2 = Kuis::create(['ruang_id' => $r->id, 'judul' => 'Kuis 2: Menyelesaikan SPLDV', 'petunjuk' => "Kerjakan 6 soal pilihan ganda dan 1 soal esai.\nGunakan kertas buram untuk menghitung.",
            'mulai' => today()->setTime(0, 0), 'selesai' => today()->setTime(23, 59), 'durasi_menit' => 30, 'terbit' => true]);
        foreach ([
            ['Penyelesaian dari x + y = 10 dan 2x - y = 5 adalah ...', ['A' => 'x = 5, y = 5', 'B' => 'x = 4, y = 6', 'C' => 'x = 6, y = 4', 'D' => 'x = 3, y = 7'], 'A'],
            ['Dari 3x + 2y = 16 dan x + 2y = 8, nilai x adalah ...', ['A' => '2', 'B' => '3', 'C' => '4', 'D' => '6'], 'C'],
            ['Metode yang menghilangkan salah satu variabel dengan menyamakan koefisien disebut ...', ['A' => 'grafik', 'B' => 'substitusi', 'C' => 'eliminasi', 'D' => 'determinan'], 'C'],
            ['Jika 2x + y = 11 dan x - y = 1, maka nilai x + y adalah ...', ['A' => '5', 'B' => '7', 'C' => '9', 'D' => '11'], 'B'],
            ['Harga 2 buku dan 1 pensil Rp13.000, harga 1 buku dan 1 pensil Rp8.000. Harga 1 buku adalah ...', ['A' => 'Rp3.000', 'B' => 'Rp4.000', 'C' => 'Rp5.000', 'D' => 'Rp6.000'], 'C'],
            ['Langkah pertama metode substitusi adalah ...', ['A' => 'menggambar grafik', 'B' => 'menyatakan satu variabel dalam variabel lain', 'C' => 'menjumlahkan kedua persamaan', 'D' => 'mengalikan kedua persamaan'], 'B'],
        ] as $i => [$tanya, $opsi, $kunci]) {
            $k2->soal()->create(['urutan' => $i + 1, 'jenis' => 'pg', 'pertanyaan' => $tanya, 'opsi' => $opsi, 'kunci' => $kunci, 'bobot' => 2]);
        }
        $esai = $k2->soal()->create(['urutan' => 7, 'jenis' => 'esai', 'bobot' => 8,
            'pertanyaan' => "Harga 2 kabel UTP dan 3 konektor RJ45 adalah Rp41.000, sedangkan 1 kabel dan 5 konektor Rp34.500.\nTentukan harga 1 kabel dan 1 konektor. Tuliskan langkah penyelesaiannya!"]);
        $k2->load('soal');
        $nomorCoba = 0;
        foreach ($siswa->where("username", "!=", "siswa")->take(11) as $s) {
            $jawab = [];
            foreach ($k2->soal as $soal) {
                if ($soal->jenis === 'esai') {
                    $jawab[$soal->id] = "Misal kabel = x, konektor = y.\n2x + 3y = 41.000\nx + 5y = 34.500, dikali 2: 2x + 10y = 69.000\nEliminasi: 7y = 28.000, y = 4.000\nx = 34.500 - 20.000 = 14.500\nJadi kabel Rp14.500 dan konektor Rp4.000.";
                    continue;
                }
                $salah = array_values(array_diff(array_keys($soal->opsi), [$soal->kunci]));
                $jawab[$soal->id] = mt_rand(1, 100) <= 78 ? $soal->kunci : $salah[array_rand($salah)];
            }
            $mulai = now()->subMinutes(mt_rand(40, 200))->max($k2->mulai);
            $p = Percobaan::create(['kuis_id' => $k2->id, 'siswa_id' => $s->id, 'mulai_at' => $mulai, 'batas_at' => $mulai->copy()->addMinutes(30),
                'selesai_at' => $mulai->copy()->addMinutes(mt_rand(12, 28))->min(now()), 'urutan_soal' => ['soal' => $k2->soal->pluck('id')->all(), 'opsi' => []], 'jawaban' => $jawab]);
            // 8 percobaan pertama sudah dikoreksi guru, sisanya menunggu koreksi esai
            if ($nomorCoba++ < 8) $p->nilai_esai = [$esai->id => mt_rand(4, 8)];
            $p->nilai = $p->hitungNilai();
            $p->save();
        }

        // ---------- Diskusi ----------
        $tanya = [
            [$siswa[3], 'Bu, untuk metode eliminasi, kalau koefisiennya tidak ada yang sama boleh dikali dua-duanya?', [[$guru, 'Boleh. Kalikan kedua persamaan dengan bilangan yang membuat koefisien salah satu variabel sama, misalnya memakai KPK-nya.'], [$siswa[7], 'Terima kasih Bu, jadi lebih paham.']]],
            [$siswa[0], 'Bu, tugas latihan 2 boleh dikumpulkan dalam bentuk foto JPG?', [[$guru, 'Boleh, asalkan tulisannya terbaca jelas. Kalau lebih dari satu foto, gabungkan jadi PDF ya.']]],
            [$siswa[10], 'Untuk proyek mini, soal ceritanya boleh dari harga di warung dekat rumah?', []],
        ];
        foreach ($tanya as $j => [$penanya, $isi, $balas]) {
            $waktu = now()->subDays(6 - $j * 2);
            $d = Diskusi::create(['ruang_id' => $r->id, 'user_id' => $penanya->id, 'isi' => $isi]);
            $d->forceFill(['created_at' => $waktu])->save();
            foreach ($balas as $b => [$u, $teks]) {
                $x = Diskusi::create(['ruang_id' => $r->id, 'user_id' => $u->id, 'parent_id' => $d->id, 'isi' => $teks]);
                $x->forceFill(['created_at' => $waktu->copy()->addHours(2 + $b * 3)])->save();
            }
        }
    }

    /** Nama siswa contoh (tanpa library Faker agar seeder jalan dengan composer install --no-dev) */
    private function namaAcak(string $jk): string
    {
        $depanL = ['Aditya', 'Bagas', 'Dimas', 'Fajar', 'Galih', 'Hafiz', 'Ilham', 'Joko', 'Kevin', 'Lutfi', 'Muhammad', 'Naufal', 'Rizky', 'Satria', 'Taufik', 'Wahyu', 'Yusuf', 'Zaki', 'Arif', 'Bima', 'Dafa', 'Eko', 'Farhan', 'Gilang', 'Hendra', 'Irfan', 'Rangga', 'Rafi', 'Andika', 'Putra'];
        $depanP = ['Aisyah', 'Bunga', 'Citra', 'Dewi', 'Elsa', 'Fitri', 'Gita', 'Hana', 'Indah', 'Kirana', 'Laila', 'Maya', 'Nabila', 'Putri', 'Rahma', 'Salsabila', 'Tiara', 'Wulan', 'Yuni', 'Zahra', 'Anisa', 'Dinda', 'Intan', 'Nadia', 'Ratna', 'Sekar', 'Vina', 'Amelia', 'Kartika', 'Syifa'];
        $belakang = ['Pratama', 'Saputra', 'Wijaya', 'Nugroho', 'Setiawan', 'Hidayat', 'Kurniawan', 'Ramadhan', 'Santoso', 'Firmansyah', 'Lestari', 'Rahmawati', 'Anggraini', 'Wulandari', 'Permata', 'Sari', 'Maharani', 'Puspitasari', 'Hakim', 'Susanto', 'Purnama', 'Utami', 'Siregar', 'Nasution', 'Simanjuntak', 'Harahap', 'Gunawan', 'Prasetyo', 'Handayani', 'Kusuma'];
        $depan = $jk === 'L' ? $depanL : $depanP;
        return $depan[mt_rand(0, count($depan) - 1)] . ' ' . $belakang[mt_rand(0, count($belakang) - 1)];
    }

    /** Isi presensi acak; $kecuali = username yang sengaja dikosongkan */
    private function isiPresensi(Pertemuan $p, $siswa, int $persenHadir, array $kecuali = []): void
    {
        $tutup = $p->presensi_tutup->isPast();
        $berjalan = max(6, (int) $p->presensi_buka->diffInMinutes(now(), true));
        foreach ($siswa as $s) {
            if (in_array($s->username, $kecuali, true)) continue;
            $acak = mt_rand(1, 100);
            if ($acak <= $persenHadir) {
                Presensi::create(['pertemuan_id' => $p->id, 'siswa_id' => $s->id, 'status' => 'hadir',
                    'waktu' => $p->presensi_buka->copy()->addMinutes(mt_rand(5, $tutup ? 300 : min(300, $berjalan)))]);
            } elseif ($tutup && $acak <= $persenHadir + 5) {
                Presensi::create(['pertemuan_id' => $p->id, 'siswa_id' => $s->id, 'status' => $acak % 2 ? 'izin' : 'sakit',
                    'keterangan' => $acak % 2 ? 'Acara keluarga' : 'Demam, surat dari orang tua', 'waktu' => $p->presensi_buka, 'oleh_guru' => true]);
            }
        }
    }

    /** PDF satu halaman berisi baris teks (untuk lampiran contoh, tanpa library tambahan) */
    private function pdfSederhana(array $baris): string
    {
        $teks = "BT /F1 13 Tf 60 780 Td 18 TL\n";
        foreach ($baris as $l) $teks .= '(' . str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $l) . ") Tj T*\n";
        $teks .= 'ET';
        $obj = [
            '<< /Type /Catalog /Pages 2 0 R >>',
            '<< /Type /Pages /Kids [3 0 R] /Count 1 >>',
            '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Contents 4 0 R /Resources << /Font << /F1 5 0 R >> >> >>',
            '<< /Length ' . strlen($teks) . " >>\nstream\n$teks\nendstream",
            '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>',
        ];
        $pdf = "%PDF-1.4\n";
        $posisi = [];
        foreach ($obj as $i => $o) {
            $posisi[] = strlen($pdf);
            $pdf .= ($i + 1) . " 0 obj\n$o\nendobj\n";
        }
        $xref = strlen($pdf);
        $pdf .= "xref\n0 " . (count($obj) + 1) . "\n0000000000 65535 f \n";
        foreach ($posisi as $o) $pdf .= sprintf("%010d 00000 n \n", $o);
        return $pdf . "trailer\n<< /Size " . (count($obj) + 1) . " /Root 1 0 R >>\nstartxref\n$xref\n%%EOF";
    }
}
