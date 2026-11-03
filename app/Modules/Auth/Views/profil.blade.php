@extends('layouts.app')
@section('title', 'Profil')
@section('content')
@php($u = auth()->user())
<div class="kepala"><div><h1><span class="sorot">Profil saya</span></h1><p>Data akun dan penggantian password.</p></div></div>
<div class="grid g2">
  <div class="kartu">
    <h2>{!! icon('user', 18) !!}Data akun</h2>
    <table>
      <tr><td class="kecil-redup">Nama</td><td class="tebal">{{ $u->name }}</td></tr>
      <tr><td class="kecil-redup">{{ $u->role === 'siswa' ? 'NISN' : ($u->role === 'guru' ? 'NIP' : 'Username') }}</td><td>{{ $u->username }}</td></tr>
      <tr><td class="kecil-redup">Peran</td><td>{{ \App\Modules\Pengguna\Models\User::ROLES[$u->role] }}</td></tr>
      @if ($u->kelas)<tr><td class="kecil-redup">Kelas</td><td>{{ $u->kelas->nama }}</td></tr>@endif
      @if ($u->email)<tr><td class="kecil-redup">Email</td><td>{{ $u->email }}</td></tr>@endif
    </table>
    <p class="kecil-redup mt">Perubahan nama atau NIP/NISN dilakukan oleh admin sekolah.</p>
  </div>
  <div class="kartu">
    <h2>{!! icon('key', 18) !!}Ganti password</h2>
    <form method="post">
      @csrf
      <div class="field"><label>Password lama</label><input type="password" name="password_lama" required autocomplete="current-password"></div>
      <div class="field"><label>Password baru</label><input type="password" name="password" required minlength="6" autocomplete="new-password"><small>Minimal 6 karakter.</small></div>
      <div class="field"><label>Ulangi password baru</label><input type="password" name="password_confirmation" required autocomplete="new-password"></div>
      <button class="btn">{!! icon('check', 16) !!}Simpan password</button>
    </form>
  </div>
</div>
@endsection
