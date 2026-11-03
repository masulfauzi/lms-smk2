<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengaturan', function (Blueprint $t) {
            $t->string('kunci', 60)->primary();
            $t->text('nilai')->nullable();
        });

        Schema::create('tahun_ajaran', function (Blueprint $t) {
            $t->id();
            $t->string('nama', 20); // 2026/2027
            $t->enum('semester', ['Ganjil', 'Genap']);
            $t->boolean('aktif')->default(false);
            $t->timestamps();
            $t->unique(['nama', 'semester']);
        });

        Schema::create('jurusan', function (Blueprint $t) {
            $t->id();
            $t->string('kode', 15)->unique();
            $t->string('nama', 120);
            $t->timestamps();
        });

        Schema::create('kelas', function (Blueprint $t) {
            $t->id();
            $t->unsignedTinyInteger('tingkat'); // 1-12
            $t->foreignId('jurusan_id')->nullable()->constrained('jurusan')->nullOnDelete();
            $t->string('nama', 40)->unique(); // contoh: VII A, X TKJ 1
            $t->foreignId('wali_id')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamps();
        });

        Schema::table('users', function (Blueprint $t) {
            $t->foreign('kelas_id')->references('id')->on('kelas')->nullOnDelete();
        });

        Schema::create('mapel', function (Blueprint $t) {
            $t->id();
            $t->string('kode', 15)->unique();
            $t->string('nama', 120);
            $t->enum('kelompok', ['Umum', 'Kejuruan', 'Muatan Lokal'])->default('Umum');
            $t->timestamps();
        });

        // Ruang belajar = satu mapel di satu kelas, diampu satu guru, pada satu tahun ajaran
        Schema::create('ruang', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tahun_ajaran_id')->constrained('tahun_ajaran')->cascadeOnDelete();
            $t->foreignId('kelas_id')->constrained('kelas')->cascadeOnDelete();
            $t->foreignId('mapel_id')->constrained('mapel')->cascadeOnDelete();
            $t->foreignId('guru_id')->nullable()->constrained('users')->nullOnDelete();
            $t->text('deskripsi')->nullable();
            $t->unsignedTinyInteger('kkm')->default(75);
            $t->unsignedTinyInteger('bobot_tugas')->default(40); // persen
            $t->unsignedTinyInteger('bobot_kuis')->default(40);
            $t->unsignedTinyInteger('bobot_kehadiran')->default(20);
            $t->timestamps();
            $t->unique(['tahun_ajaran_id', 'kelas_id', 'mapel_id']);
        });

        Schema::create('jadwal', function (Blueprint $t) {
            $t->id();
            $t->foreignId('ruang_id')->constrained('ruang')->cascadeOnDelete();
            $t->unsignedTinyInteger('hari'); // 1 = Senin ... 6 = Sabtu
            $t->time('jam_mulai');
            $t->time('jam_selesai');
            $t->string('link_meet')->nullable();
            $t->timestamps();
        });

        // Pertemuan: materi + presensi daring dalam satu sesi
        Schema::create('pertemuan', function (Blueprint $t) {
            $t->id();
            $t->foreignId('ruang_id')->constrained('ruang')->cascadeOnDelete();
            $t->unsignedSmallInteger('urutan');
            $t->string('judul', 160);
            $t->longText('isi')->nullable();
            $t->string('video_url')->nullable();
            $t->string('lampiran_path')->nullable();
            $t->string('lampiran_nama')->nullable();
            $t->date('tanggal');
            $t->dateTime('presensi_buka')->nullable();
            $t->dateTime('presensi_tutup')->nullable();
            $t->boolean('terbit')->default(true);
            $t->timestamps();
            $t->index(['ruang_id', 'urutan']);
        });

        Schema::create('materi_selesai', function (Blueprint $t) {
            $t->foreignId('pertemuan_id')->constrained('pertemuan')->cascadeOnDelete();
            $t->foreignId('siswa_id')->constrained('users')->cascadeOnDelete();
            $t->timestamp('selesai_at');
            $t->primary(['pertemuan_id', 'siswa_id']);
        });

        Schema::create('presensi', function (Blueprint $t) {
            $t->id();
            $t->foreignId('pertemuan_id')->constrained('pertemuan')->cascadeOnDelete();
            $t->foreignId('siswa_id')->constrained('users')->cascadeOnDelete();
            $t->enum('status', ['hadir', 'izin', 'sakit', 'alpa']);
            $t->timestamp('waktu')->nullable();
            $t->string('keterangan', 255)->nullable();
            $t->boolean('oleh_guru')->default(false);
            $t->timestamps();
            $t->unique(['pertemuan_id', 'siswa_id']);
        });

        Schema::create('tugas', function (Blueprint $t) {
            $t->id();
            $t->foreignId('ruang_id')->constrained('ruang')->cascadeOnDelete();
            $t->foreignId('pertemuan_id')->nullable()->constrained('pertemuan')->nullOnDelete();
            $t->string('judul', 160);
            $t->longText('instruksi')->nullable();
            $t->string('lampiran_path')->nullable();
            $t->string('lampiran_nama')->nullable();
            $t->dateTime('tenggat');
            $t->enum('jenis_kumpul', ['file', 'teks', 'keduanya'])->default('keduanya');
            $t->boolean('boleh_terlambat')->default(true);
            $t->timestamps();
        });

        Schema::create('pengumpulan', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tugas_id')->constrained('tugas')->cascadeOnDelete();
            $t->foreignId('siswa_id')->constrained('users')->cascadeOnDelete();
            $t->longText('teks')->nullable();
            $t->string('file_path')->nullable();
            $t->string('file_nama')->nullable();
            $t->timestamp('dikumpulkan_at');
            $t->boolean('terlambat')->default(false);
            $t->unsignedTinyInteger('nilai')->nullable();
            $t->text('komentar')->nullable();
            $t->timestamp('dinilai_at')->nullable();
            $t->timestamps();
            $t->unique(['tugas_id', 'siswa_id']);
        });

        Schema::create('kuis', function (Blueprint $t) {
            $t->id();
            $t->foreignId('ruang_id')->constrained('ruang')->cascadeOnDelete();
            $t->string('judul', 160);
            $t->text('petunjuk')->nullable();
            $t->dateTime('mulai');
            $t->dateTime('selesai');
            $t->unsignedSmallInteger('durasi_menit')->default(60);
            $t->boolean('acak_soal')->default(true);
            $t->boolean('acak_opsi')->default(true);
            $t->boolean('tampilkan_nilai')->default(true);
            $t->boolean('terbit')->default(false);
            $t->timestamps();
        });

        Schema::create('soal', function (Blueprint $t) {
            $t->id();
            $t->foreignId('kuis_id')->constrained('kuis')->cascadeOnDelete();
            $t->unsignedSmallInteger('urutan');
            $t->enum('jenis', ['pg', 'esai'])->default('pg');
            $t->text('pertanyaan');
            $t->json('opsi')->nullable(); // {"A": "...", "B": "..."}
            $t->string('kunci', 1)->nullable();
            $t->unsignedTinyInteger('bobot')->default(1);
            $t->timestamps();
        });

        Schema::create('percobaan', function (Blueprint $t) {
            $t->id();
            $t->foreignId('kuis_id')->constrained('kuis')->cascadeOnDelete();
            $t->foreignId('siswa_id')->constrained('users')->cascadeOnDelete();
            $t->timestamp('mulai_at');
            $t->timestamp('batas_at');
            $t->timestamp('selesai_at')->nullable();
            $t->json('urutan_soal'); // [soal_id, ...] dan urutan opsi per soal
            $t->json('jawaban')->nullable(); // {soal_id: "A" | "teks esai"}
            $t->json('nilai_esai')->nullable(); // {soal_id: skor 0..bobot}
            $t->decimal('nilai', 5, 2)->nullable(); // 0-100, null bila esai belum dikoreksi
            $t->timestamps();
            $t->unique(['kuis_id', 'siswa_id']);
        });

        Schema::create('diskusi', function (Blueprint $t) {
            $t->id();
            $t->foreignId('ruang_id')->constrained('ruang')->cascadeOnDelete();
            $t->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $t->foreignId('parent_id')->nullable()->constrained('diskusi')->cascadeOnDelete();
            $t->text('isi');
            $t->timestamps();
        });

        Schema::create('pengumuman', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $t->string('judul', 160);
            $t->text('isi');
            $t->enum('sasaran', ['semua', 'guru', 'siswa'])->default('semua');
            $t->foreignId('kelas_id')->nullable()->constrained('kelas')->cascadeOnDelete();
            $t->boolean('disematkan')->default(false);
            $t->timestamps();
        });
    }

    public function down(): void
    {
        foreach (['pengumuman', 'diskusi', 'percobaan', 'soal', 'kuis', 'pengumpulan', 'tugas', 'presensi', 'materi_selesai', 'pertemuan', 'jadwal', 'ruang', 'mapel'] as $tbl) {
            Schema::dropIfExists($tbl);
        }
        Schema::table('users', fn (Blueprint $t) => $t->dropForeign(['kelas_id']));
        foreach (['kelas', 'jurusan', 'tahun_ajaran', 'pengaturan'] as $tbl) {
            Schema::dropIfExists($tbl);
        }
    }
};
