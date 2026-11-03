@extends('layouts.app')
@section('title', 'Tugas · ' . $ruang->judul())
@section('content')
@php($u = auth()->user())
@include('ruang::_kepala')
<div class="kartu">
  @forelse ($tugas as $t)
    @if ($loop->first)<ul class="daftar">@endif
    @php($lewat = $t->lewatTenggat())
    <li>
      <span class="ikon {{ $lewat ? '' : 'kuning' }}">{!! icon('clipboard', 18) !!}</span>
      <span class="teks"><b class="potong"><a href="{{ route('tugas.show', [$ruang, $t]) }}">{{ $t->judul }}</a></b>
        <small>Tenggat {{ tgl($t->tenggat, true) }} · {{ \App\Modules\Tugas\Models\Tugas::JENIS[$t->jenis_kumpul] }}{{ $t->boleh_terlambat ? '' : ' · tidak menerima terlambat' }}</small></span>
      @if ($u->role === 'siswa')
        @php($k = $milikSaya[$t->id] ?? null)
        @if ($k && $k->nilai !== null)<span class="lencana hijau">Nilai {{ $k->nilai }}</span>
        @elseif ($k)<span class="lencana biru">{{ $k->terlambat ? 'Terkumpul (terlambat)' : 'Terkumpul' }}</span>
        @elseif ($lewat)<span class="lencana merah">{{ $t->boleh_terlambat ? 'Terlambat' : 'Tidak mengumpulkan' }}</span>
        @else<span class="lencana oranye">{{ sisa_waktu($t->tenggat) }}</span>@endif
      @else
        <span class="lencana biru">{{ $t->pengumpulan_count }}/{{ $jumlahSiswa }} terkumpul</span>
        @if ($t->belum_dinilai_count)<span class="lencana kuning">{{ $t->belum_dinilai_count }} perlu dinilai</span>@endif
      @endif
    </li>
    @if ($loop->last)</ul>@endif
  @empty
    @include('partials.kosong', ['ikon' => 'clipboard', 'judul' => 'Belum ada tugas'])
  @endforelse
</div>
@endsection
