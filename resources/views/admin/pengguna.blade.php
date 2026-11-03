@extends('layouts.app')
@section('title', ucfirst($role))
@section('content')
@php($siswa = $role === 'siswa')
<div class="kepala">
  <div><h1><span class="sorot">{{ $siswa ? 'Data siswa' : 'Data guru' }}</span></h1><p>Login memakai {{ $siswa ? 'NISN' : 'NIP' }}. Password awal = {{ $siswa ? 'NISN' : 'NIP' }} bila dikosongkan saat impor.</p></div>
  <form class="inline-form" method="get">
    @if ($siswa)<div class="field"><select name="kelas" onchange="this.form.submit()"><option value="">Semua kelas</option>@foreach ($kelas as $k)<option value="{{ $k->id }}" @selected(request('kelas') == $k->id)>{{ $k->nama }}</option>@endforeach</select></div>@endif
    <div class="field"><input type="search" name="cari" value="{{ request('cari') }}" placeholder="Cari nama / nomor"></div>
    <button class="btn garis">{!! icon('search', 16) !!}</button>
  </form>
</div>
<div class="grid g-utama">
  <div class="kartu rapat">
    <div class="tabel-wrap"><table>
      <thead><tr><th>Nama</th><th>{{ $siswa ? 'NISN' : 'NIP' }}</th>@if ($siswa)<th>Kelas</th>@endif<th>L/P</th><th>Status</th><th>Terakhir aktif</th><th></th></tr></thead>
      <tbody>
        @forelse ($daftar as $p)
          <tr><td class="tebal">{{ $p->name }}</td><td>{{ $p->username }}</td>@if ($siswa)<td>{{ $p->kelas?->nama ?? '-' }}</td>@endif<td>{{ $p->jk ?? '-' }}</td>
            <td>@if ($p->aktif)<span class="lencana hijau">Aktif</span>@else<span class="lencana">Nonaktif</span>@endif</td>
            <td class="kecil-redup">{{ $p->terakhir_aktif_at?->locale('id')->diffForHumans() ?? '-' }}</td>
            <td class="angka" style="white-space:nowrap">
              <a class="ikon-btn" href="{{ request()->fullUrlWithQuery(['ubah' => $p->id]) }}" title="Ubah">{!! icon('edit', 16) !!}</a>
              <form method="post" action="{{ route('admin.pengguna.reset', $p) }}" style="display:inline" data-konfirmasi="Reset password {{ $p->name }} menjadi {{ $siswa ? 'NISN' : 'NIP' }}-nya?">@csrf<button class="ikon-btn" title="Reset password">{!! icon('key', 16) !!}</button></form>
              @include('partials.hapus', ['url' => route('admin.pengguna.hapus', $p), 'pesan' => 'Hapus ' . $p->name . '? Semua tugas, presensi, dan nilainya ikut terhapus. Untuk lulus/pindah, cukup nonaktifkan.'])
            </td></tr>
        @empty
          <tr><td colspan="7">@include('partials.kosong', ['ikon' => 'users', 'judul' => 'Tidak ada data'])</td></tr>
        @endforelse
      </tbody>
    </table></div>
    {{ $daftar->links() }}
  </div>
  <div>
    <form method="post" class="kartu" action="{{ route('admin.pengguna', $role) }}">
      @csrf <input type="hidden" name="id" value="{{ $ubah?->id }}">
      <h2>{!! icon($ubah ? 'edit' : 'plus', 18) !!}{{ $ubah ? 'Ubah ' . $role : 'Tambah ' . $role }}</h2>
      <div class="field"><label>Nama lengkap</label><input type="text" name="name" value="{{ old('name', $ubah?->name) }}" required></div>
      <div class="field"><label>{{ $siswa ? 'NISN' : 'NIP' }} (untuk login)</label><input type="text" name="username" value="{{ old('username', $ubah?->username) }}" required></div>
      <div class="baris-form">
        <div class="field"><label>L/P</label><select name="jk"><option value="">-</option><option value="L" @selected(old('jk', $ubah?->jk) === 'L')>L</option><option value="P" @selected(old('jk', $ubah?->jk) === 'P')>P</option></select></div>
        @if ($siswa)<div class="field"><label>Kelas</label><select name="kelas_id" required>@foreach ($kelas as $k)<option value="{{ $k->id }}" @selected((int) old('kelas_id', $ubah?->kelas_id ?? request('kelas')) === $k->id)>{{ $k->nama }}</option>@endforeach</select></div>@endif
      </div>
      <div class="field"><label>Email (opsional)</label><input type="email" name="email" value="{{ old('email', $ubah?->email) }}"></div>
      <div class="field"><label>Password {{ $ubah ? '(kosongkan bila tidak diganti)' : '' }}</label><input type="text" name="password" {{ $ubah ? '' : 'required' }} minlength="6" autocomplete="new-password"></div>
      <div class="field"><label class="cek"><input type="checkbox" name="aktif" value="1" @checked(old('aktif', $ubah?->aktif ?? true))> Akun aktif</label></div>
      <button class="btn">{!! icon('check', 16) !!}Simpan</button> @if ($ubah)<a class="btn garis" href="{{ route('admin.pengguna', $role) }}">Batal</a>@endif
    </form>
    <form method="post" enctype="multipart/form-data" action="{{ route('admin.pengguna.impor', $role) }}" class="kartu">
      @csrf
      <h2>{!! icon('upload', 18) !!}Impor dari Excel (CSV)</h2>
      <p class="kecil-redup">Kolom: <code>nama;{{ $siswa ? 'nisn' : 'nip' }};jk;kelas;password</code>. Simpan dari Excel sebagai CSV. {{ $siswa ? 'Nama kelas harus sama persis dengan data kelas.' : '' }}</p>
      <div class="field"><input type="file" name="csv" accept=".csv,.txt" required></div>
      <div style="display:flex;gap:8px;flex-wrap:wrap"><button class="btn garis">{!! icon('upload', 16) !!}Impor</button><a class="btn garis" href="{{ route('admin.pengguna.contoh', $role) }}">{!! icon('download', 16) !!}Contoh CSV</a></div>
    </form>
  </div>
</div>
@endsection
