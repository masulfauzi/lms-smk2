@extends('layouts.app')
@section('title', 'Pengumuman')
@section('content')
@php($u = auth()->user())
<div class="kepala"><div><h1><span class="sorot">Pengumuman</span></h1><p>Informasi dari sekolah dan guru.</p></div></div>
<div class="grid {{ $u->role === 'siswa' ? '' : 'g-utama' }}">
  <div class="kartu">
    @forelse ($daftar as $p)
      <article style="padding:14px 0;border-bottom:1px dashed var(--garis-2)">
        <div style="display:flex;gap:10px;align-items:flex-start">
          <div style="flex:1;min-width:0">
            <h3 style="font-size:16px;display:flex;gap:6px;align-items:center;flex-wrap:wrap">@if ($p->disematkan)<span class="lencana kuning">{!! icon('pin', 12) !!}Disematkan</span>@endif{{ $p->judul }}</h3>
            <div class="kecil-redup">{{ $p->user->name }} · {{ tgl($p->created_at, true) }} ·
              {{ $p->kelas ? 'Kelas ' . $p->kelas->nama : ['semua' => 'Semua warga sekolah', 'guru' => 'Guru', 'siswa' => 'Semua siswa'][$p->sasaran] }}</div>
          </div>
          @if ($u->role === 'admin' || $p->user_id === $u->id)@include('partials.hapus', ['url' => route('pengumuman.hapus', $p), 'pesan' => 'Hapus pengumuman ini?'])@endif
        </div>
        <div class="materi" style="font-size:14.5px;margin-top:8px">{!! teks($p->isi) !!}</div>
      </article>
    @empty
      @include('partials.kosong', ['ikon' => 'megaphone', 'judul' => 'Belum ada pengumuman'])
    @endforelse
    {{ $daftar->links() }}
  </div>
  @if ($u->role !== 'siswa')
  <div class="kartu">
    <h2>{!! icon('plus', 18) !!}Buat pengumuman</h2>
    <form method="post" action="{{ route('pengumuman') }}">
      @csrf
      <div class="field"><label>Judul</label><input type="text" name="judul" value="{{ old('judul') }}" required maxlength="160"></div>
      <div class="field"><label>Isi</label><textarea name="isi" required>{{ old('isi') }}</textarea></div>
      @if ($u->role === 'admin')
        <div class="field"><label>Ditujukan untuk</label>
          <select name="sasaran"><option value="semua">Semua warga sekolah</option><option value="guru">Guru saja</option><option value="siswa">Siswa saja</option></select></div>
        <div class="field"><label>Khusus kelas (opsional)</label>
          <select name="kelas_id"><option value="">Semua kelas</option>@foreach ($kelas as $k)<option value="{{ $k->id }}">{{ $k->nama }}</option>@endforeach</select></div>
        <div class="field"><label class="cek"><input type="checkbox" name="disematkan" value="1"> Sematkan di atas</label></div>
      @else
        <input type="hidden" name="sasaran" value="siswa">
        <div class="field"><label>Untuk siswa kelas</label>
          <select name="kelas_id" required>@foreach ($kelas as $k)<option value="{{ $k->id }}">{{ $k->nama }}</option>@endforeach</select></div>
      @endif
      <button class="btn">{!! icon('megaphone', 16) !!}Terbitkan</button>
    </form>
  </div>
  @endif
</div>
@endsection
