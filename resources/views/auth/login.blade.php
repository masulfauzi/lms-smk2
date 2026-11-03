<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Masuk · {{ setting('nama_sekolah') }}</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Lexend:wght@400;500;600;700&family=Kalam:wght@400;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('css/app.css') }}?v=1">
</head>
<body>
<div class="login-halaman">
  <section class="login-kiri">
    <div class="merek" style="border:0;padding:0">
      <div class="logo">{!! icon('school', 22) !!}</div>
      <div><b>{{ setting('nama_sekolah') }}</b><small>NPSN {{ setting('npsn') }}</small></div>
    </div>
    <div>
      <div class="tulisan">Belajar dari rumah,</div>
      <h1>tetap <span>terarah</span> bersama guru di kelas daring.</h1>
      <p>Materi, presensi, tugas, kuis, dan nilai jenjang {{ setting('jenjang') }} ada di satu tempat.</p>
      <div class="login-poin">
        <div>{!! icon('video') !!}Materi teks, video, dan lampiran per pertemuan</div>
        <div>{!! icon('check-square') !!}Presensi daring dengan jam buka-tutup</div>
        <div>{!! icon('clipboard') !!}Kumpulkan tugas dan lihat nilai serta komentar guru</div>
        <div>{!! icon('quiz') !!}Kuis berwaktu dengan nilai otomatis</div>
      </div>
    </div>
    <small style="color:#8794c4;position:relative;z-index:1">{{ setting('alamat') }}</small>
  </section>
  <section class="login-kanan">
    <div class="login-kotak">
      <h2><span class="sorot">Masuk</span></h2>
      <p style="color:var(--redup)">Gunakan NIP (guru) atau NISN (siswa) sebagai nama pengguna.</p>
      @include('partials.flash')
      <form method="post" action="{{ url('/login') }}" class="mt">
        @csrf
        <div class="field">
          <label for="username">NIP / NISN</label>
          <input type="text" id="username" name="username" value="{{ old('username') }}" autocomplete="username" required autofocus>
        </div>
        <div class="field">
          <label for="password">Password</label>
          <input type="password" id="password" name="password" autocomplete="current-password" required>
        </div>
        <div class="field"><label class="cek"><input type="checkbox" name="ingat" value="1"> Ingat saya di perangkat ini</label></div>
        <button class="btn blok">{!! icon('arrow', 18) !!}Masuk</button>
      </form>
      @if (config('app.env') !== 'production')
      <div class="demo kartu" style="padding:12px 14px">
        <b style="color:var(--tinta)">Akun demo</b>
        <table>
          <tr><td>Admin</td><td><code>admin</code></td><td><code>admin123</code></td></tr>
          <tr><td>Guru</td><td><code>guru</code></td><td><code>guru123</code></td></tr>
          <tr><td>Siswa</td><td><code>siswa</code></td><td><code>siswa123</code></td></tr>
        </table>
      </div>
      @endif
    </div>
  </section>
</div>
</body>
</html>
