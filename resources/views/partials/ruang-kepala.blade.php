@php($u = auth()->user())
<div class="ruang-kepala">
  <div class="isi">
    <span class="pita" style="background: {{ $ruang->warna() }}"></span>
    <div style="flex:1;min-width:220px">
      <div class="remah"><a href="{{ route('ruang') }}">Ruang Belajar</a> / {{ $ruang->kelas->nama }}</div>
      <h1>{{ $ruang->mapel->nama }}</h1>
      <div class="meta">
        <span>{!! icon('grid', 15) !!}{{ $ruang->kelas->nama }}</span>
        <span>{!! icon('user', 15) !!}{{ $ruang->guru?->name ?? 'Guru belum ditentukan' }}</span>
        <span>{!! icon('flag', 15) !!}KKM {{ $ruang->kkm }}</span>
      </div>
    </div>
    @if ($u->role !== 'siswa')
      <div class="aksi" style="display:flex;gap:8px;flex-wrap:wrap">
        <a class="btn" href="{{ route('pertemuan.create', $ruang) }}">{!! icon('plus', 16) !!}Pertemuan</a>
        <a class="btn garis" href="{{ route('tugas.create', $ruang) }}">{!! icon('clipboard', 16) !!}Tugas</a>
        <a class="btn garis" href="{{ route('kuis.create', $ruang) }}">{!! icon('quiz', 16) !!}Kuis</a>
      </div>
    @endif
  </div>
  <nav class="tab">
    <a href="{{ route('ruang.show', $ruang) }}" class="{{ request()->routeIs('ruang.show', 'pertemuan.*') ? 'on' : '' }}">{!! icon('book', 16) !!}Materi</a>
    <a href="{{ route('tugas.index', $ruang) }}" class="{{ request()->routeIs('tugas.*') ? 'on' : '' }}">{!! icon('clipboard', 16) !!}Tugas</a>
    <a href="{{ route('kuis.index', $ruang) }}" class="{{ request()->routeIs('kuis.*') ? 'on' : '' }}">{!! icon('quiz', 16) !!}Kuis</a>
    <a href="{{ route('ruang.presensi', $ruang) }}" class="{{ request()->routeIs('ruang.presensi') ? 'on' : '' }}">{!! icon('check-square', 16) !!}Presensi</a>
    <a href="{{ route('ruang.nilai', $ruang) }}" class="{{ request()->routeIs('ruang.nilai') ? 'on' : '' }}">{!! icon('award', 16) !!}Nilai</a>
    <a href="{{ route('ruang.diskusi', $ruang) }}" class="{{ request()->routeIs('ruang.diskusi') ? 'on' : '' }}">{!! icon('message', 16) !!}Diskusi</a>
    <a href="{{ route('ruang.anggota', $ruang) }}" class="{{ request()->routeIs('ruang.anggota') ? 'on' : '' }}">{!! icon('users', 16) !!}Anggota</a>
    @if ($u->role !== 'siswa')
      <a href="{{ route('ruang.pengaturan', $ruang) }}" class="{{ request()->routeIs('ruang.pengaturan') ? 'on' : '' }}">{!! icon('settings', 16) !!}Pengaturan</a>
    @endif
  </nav>
</div>
