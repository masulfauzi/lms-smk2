@extends('layouts.app')
@section('title', $ruang->judul())
@section('content')
@php($u = auth()->user())
@include('ruang::_kepala')
<div class="grid g-utama">
  <div>
    @if ($pertemuan->isEmpty())
      <div class="kartu">@include('partials.kosong', ['judul' => 'Belum ada pertemuan', 'teks' => $u->role === 'siswa' ? 'Guru belum mengunggah materi.' : 'Klik tombol "Pertemuan" untuk menambah materi pertama.'])</div>
    @else
      <div class="pertemuan">
        @foreach ($pertemuan as $p)
          @php($beres = $u->role === 'siswa' && isset($selesaiSaya[$p->id]))
          <div class="pt {{ $beres ? 'selesai' : '' }} {{ $p->terbit ? '' : 'draf' }}">
            <div class="no"><small>PERT.</small>{{ $p->urutan }}</div>
            <h3><a href="{{ route('pertemuan.show', [$ruang, $p]) }}">{{ $p->judul }}</a></h3>
            <div class="kecil-redup">{{ tgl_panjang($p->tanggal) }}</div>
            <div class="meta">
              @unless ($p->terbit)<span class="lencana">Draf</span>@endunless
              @if ($p->video_url)<span class="lencana biru">{!! icon('video', 12) !!}Video</span>@endif
              @if ($p->lampiran_path)<span class="lencana biru">{!! icon('paperclip', 12) !!}Lampiran</span>@endif
              @if ($p->presensi_buka)
                @if ($u->role === 'siswa')
                  @php($st = $presensiSaya[$p->id] ?? null)
                  @if ($st)<span class="lencana {{ $st === 'hadir' ? 'hijau' : ($st === 'alpa' ? 'merah' : 'biru') }}">{{ \App\Modules\Pertemuan\Models\Presensi::STATUS[$st] }}</span>
                  @elseif ($p->presensiTerbuka())<span class="lencana hijau">Presensi dibuka s.d. {{ $p->presensi_tutup->format('H.i') }}</span>
                  @elseif (now()->lt($p->presensi_buka))<span class="lencana">Presensi {{ $p->presensi_buka->format('H.i') }}</span>
                  @else<span class="lencana merah">Tidak presensi</span>@endif
                @else
                  <span class="lencana {{ $p->presensiTerbuka() ? 'hijau' : '' }}">{!! icon('check-square', 12) !!}Hadir {{ $p->hadir_count }}/{{ $jumlahSiswa }}</span>
                @endif
              @endif
              @if ($beres)<span class="lencana hijau">{!! icon('check', 12) !!}Sudah dipelajari</span>@endif
            </div>
          </div>
        @endforeach
      </div>
    @endif
  </div>
  <div>
    @if ($ruang->deskripsi)
      <div class="catatan" style="margin-bottom:16px"><div class="tulisan" style="font-size:19px;color:var(--margin)">Catatan guru</div><div style="font-size:14px">{!! teks($ruang->deskripsi) !!}</div></div>
    @endif
    <div class="kartu">
      <h2>{!! icon('calendar', 18) !!}Jadwal kelas daring</h2>
      @forelse ($ruang->jadwal as $j)
        <div class="slot" style="border-left-color: {{ $ruang->warna() }}">
          <b>{{ \App\Modules\Ruang\Models\Jadwal::HARI[$j->hari] }}, {{ substr($j->jam_mulai, 0, 5) }}–{{ substr($j->jam_selesai, 0, 5) }}</b>
          @if ($j->link_meet)<a class="kecil-redup" href="{{ $j->link_meet }}" target="_blank" rel="noopener">{{ \Illuminate\Support\Str::limit($j->link_meet, 40) }}</a>@else<small>Belum ada link</small>@endif
        </div>
      @empty
        <p class="kecil-redup">Jadwal belum diatur.</p>
      @endforelse
    </div>
  </div>
</div>
@endsection
