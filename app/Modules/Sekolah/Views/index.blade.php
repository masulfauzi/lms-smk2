@extends('layouts.app')
@section('title', 'Pengaturan sekolah')
@section('content')
<div class="kepala"><div><h1><span class="sorot">Pengaturan sekolah</span></h1><p>Profil sekolah, jenjang, dan tahun ajaran.</p></div></div>
<div class="grid g2">
  <form method="post" class="kartu">
    @csrf
    <h2>{!! icon('school', 18) !!}Profil sekolah</h2>
    <div class="field"><label>Nama sekolah</label><input type="text" name="nama_sekolah" value="{{ old('nama_sekolah', setting('nama_sekolah')) }}" required></div>
    <div class="baris-form">
      <div class="field"><label>Jenjang</label><select name="jenjang">@foreach (['SD', 'SMP', 'SMA', 'SMK'] as $j)<option @selected(setting('jenjang') === $j)>{{ $j }}</option>@endforeach</select></div>
      <div class="field"><label>NPSN</label><input type="text" name="npsn" value="{{ old('npsn', setting('npsn')) }}"></div>
      <div class="field"><label>KKM bawaan</label><input type="number" name="kkm" min="0" max="100" value="{{ old('kkm', setting('kkm')) }}" required></div>
    </div>
    <div class="field"><label>Alamat</label><input type="text" name="alamat" value="{{ old('alamat', setting('alamat')) }}"></div>
    <div class="field"><label>Kepala sekolah</label><input type="text" name="kepala_sekolah" value="{{ old('kepala_sekolah', setting('kepala_sekolah')) }}"></div>
    <p class="kecil-redup">Jenjang menentukan pilihan tingkat kelas (SD 1–6, SMP 7–9, SMA/SMK 10–12). Menu Jurusan muncul untuk SMA dan SMK.</p>
    <button class="btn">{!! icon('check', 16) !!}Simpan</button>
  </form>
  <div>
    <div class="kartu rapat">
      <h2>{!! icon('calendar', 18) !!}Tahun ajaran</h2>
      <div class="tabel-wrap mt"><table>
        <thead><tr><th>Tahun ajaran</th><th>Semester</th><th class="angka">Ruang</th><th></th></tr></thead>
        <tbody>
          @foreach ($tahun as $t)
            <tr><td class="tebal">{{ $t->nama }}</td><td>{{ $t->semester }}</td><td class="angka">{{ $t->ruang_count }}</td>
              <td class="angka">@if ($t->aktif)<span class="lencana hijau">Aktif</span>@else
                <form method="post" action="{{ route('admin.tahun.aktif', $t) }}" data-konfirmasi="Aktifkan {{ $t->label() }}? Guru & siswa akan melihat ruang belajar semester ini.">@csrf<button class="btn kecil garis">Aktifkan</button></form>@endif</td></tr>
          @endforeach
        </tbody>
      </table></div>
    </div>
    <form method="post" action="{{ route('admin.tahun.store') }}" class="kartu">
      @csrf
      <h2>{!! icon('plus', 18) !!}Tambah semester</h2>
      <div class="baris-form">
        <div class="field"><label>Tahun ajaran</label><input type="text" name="nama" placeholder="2027/2028" pattern="\d{4}/\d{4}" required></div>
        <div class="field"><label>Semester</label><select name="semester"><option>Ganjil</option><option>Genap</option></select></div>
      </div>
      <div class="field"><label class="cek"><input type="checkbox" name="salin" value="1" checked> Salin pembagian mengajar &amp; jadwal dari semester aktif</label></div>
      <button class="btn garis">{!! icon('plus', 16) !!}Tambah</button>
    </form>
  </div>
</div>
@endsection
