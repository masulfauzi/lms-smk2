@extends('layouts.app')
@section('title', $pertemuan->judul)
@section('content')
@php($u = auth()->user())
@include('partials.ruang-kepala')
<div class="grid g-utama">
  <div>
    <div class="kartu">
      <div class="remah">Pertemuan {{ $pertemuan->urutan }} · {{ tgl_panjang($pertemuan->tanggal) }} @unless ($pertemuan->terbit)<span class="lencana">Draf</span>@endunless</div>
      <h1 style="font-size:24px;margin-bottom:16px"><span class="sorot">{{ $pertemuan->judul }}</span></h1>
      @if ($id = $pertemuan->youtubeId())
        <div class="video"><iframe src="https://www.youtube-nocookie.com/embed/{{ $id }}" title="Video materi" allow="accelerometer; encrypted-media; gyroscope; picture-in-picture" allowfullscreen loading="lazy"></iframe></div>
      @elseif ($pertemuan->video_url)
        <p><a href="{{ $pertemuan->video_url }}" target="_blank" rel="noopener">{!! icon('video', 16) !!} Tonton video materi</a></p>
      @endif
      <div class="materi">{!! $pertemuan->isi ? teks($pertemuan->isi) : '<p class="kecil-redup">Tidak ada teks materi.</p>' !!}</div>
      @if ($pertemuan->lampiran_path)
        <a class="lampiran mt" href="{{ route('berkas.pertemuan', [$ruang, $pertemuan]) }}" target="_blank">
          <span class="ikon">{!! icon('file', 20) !!}</span>
          <span style="flex:1;min-width:0"><b class="potong" style="display:block">{{ $pertemuan->lampiran_nama }}</b><small class="kecil-redup">Lampiran materi · klik untuk membuka</small></span>
          {!! icon('download', 18) !!}
        </a>
      @endif
      @foreach ($pertemuan->tugas as $t)
        <a class="lampiran mt" href="{{ route('tugas.show', [$ruang, $t]) }}" style="border-color:var(--stabilo)">
          <span class="ikon" style="background:var(--stabilo-lembut);color:#8a6500">{!! icon('clipboard', 20) !!}</span>
          <span style="flex:1;min-width:0"><b class="potong" style="display:block">Tugas: {{ $t->judul }}</b><small class="kecil-redup">Tenggat {{ tgl($t->tenggat, true) }}</small></span>
          {!! icon('arrow', 18) !!}
        </a>
      @endforeach
      <div style="display:flex;justify-content:space-between;gap:10px;margin-top:24px;flex-wrap:wrap">
        @if ($sebelum)<a class="btn garis kecil" href="{{ route('pertemuan.show', [$ruang, $sebelum]) }}">{!! icon('back', 14) !!}Pertemuan {{ $sebelum->urutan }}</a>@else<span></span>@endif
        @if ($sesudah)<a class="btn garis kecil" href="{{ route('pertemuan.show', [$ruang, $sesudah]) }}">Pertemuan {{ $sesudah->urutan }}{!! icon('arrow', 14) !!}</a>@endif
      </div>
    </div>
  </div>

  <div>
    @if ($u->role === 'siswa')
      @if ($pertemuan->presensi_buka)
        @if ($presensiSaya)
          <div class="presensi-panel sudah"><div class="teks"><b>Presensi: {{ \App\Models\Presensi::STATUS[$presensiSaya->status] }}</b>
            <div class="kecil-redup">{{ $presensiSaya->waktu ? 'Tercatat ' . tgl($presensiSaya->waktu, true) : '' }}{{ $presensiSaya->keterangan ? ' · ' . $presensiSaya->keterangan : '' }}</div></div></div>
        @elseif ($pertemuan->presensiTerbuka())
          <div class="presensi-panel buka"><div class="teks"><b>Presensi dibuka</b><div class="kecil-redup">Sampai pukul {{ $pertemuan->presensi_tutup->format('H.i') }}</div></div>
            <form method="post" action="{{ route('pertemuan.hadir', [$ruang, $pertemuan]) }}">@csrf<button class="btn hijau">{!! icon('check', 16) !!}Saya hadir</button></form></div>
        @elseif (now()->lt($pertemuan->presensi_buka))
          <div class="presensi-panel"><div class="teks"><b>Presensi belum dibuka</b><div class="kecil-redup">Dibuka {{ tgl($pertemuan->presensi_buka, true) }} s.d. {{ $pertemuan->presensi_tutup->format('H.i') }}</div></div></div>
        @else
          <div class="presensi-panel" style="border-color:#f0bdb5"><div class="teks"><b style="color:var(--merah)">Presensi sudah ditutup</b><div class="kecil-redup">Kamu tercatat alpa. Hubungi guru bila ada izin/sakit.</div></div></div>
        @endif
      @endif
      <div class="kartu mt">
        <h2>{!! icon('check', 18) !!}Progres belajar</h2>
        <p class="kecil-redup">Tandai setelah kamu selesai membaca materi dan menonton videonya.</p>
        <form method="post" action="{{ route('pertemuan.selesai', [$ruang, $pertemuan]) }}">@csrf
          <button class="btn {{ $selesai ? 'garis' : 'stabilo' }} blok">{!! icon($selesai ? 'x' : 'check', 16) !!}{{ $selesai ? 'Batalkan tanda selesai' : 'Tandai sudah dipelajari' }}</button></form>
      </div>
    @else
      <div class="kartu">
        <h2>{!! icon('settings', 18) !!}Kelola</h2>
        <div style="display:flex;gap:8px;flex-wrap:wrap">
          <a class="btn garis kecil" href="{{ route('pertemuan.edit', [$ruang, $pertemuan]) }}">{!! icon('edit', 14) !!}Ubah</a>
          <a class="btn garis kecil" href="{{ route('tugas.create', $ruang) }}?pertemuan={{ $pertemuan->id }}">{!! icon('clipboard', 14) !!}Tambah tugas</a>
        </div>
        @if ($pertemuan->presensi_buka)
          <p class="kecil-redup mt">Presensi {{ tgl($pertemuan->presensi_buka, true) }} – {{ $pertemuan->presensi_tutup->format('H.i') }}
            @if ($pertemuan->presensiTerbuka())<span class="lencana hijau">Sedang dibuka</span>@endif</p>
        @endif
      </div>
    @endif
  </div>
</div>

@if ($u->role !== 'siswa' && $pertemuan->presensi_buka)
  @php($tutup = $pertemuan->presensi_tutup->isPast())
  <form method="post" action="{{ route('pertemuan.presensi', [$ruang, $pertemuan]) }}" class="kartu rapat mt">
    @csrf
    <h2>{!! icon('check-square', 18) !!}Presensi pertemuan {{ $pertemuan->urutan }}
      <span class="kanan kecil-redup">{{ $rekap->where('status', 'hadir')->count() }} hadir dari {{ $siswa->count() }} siswa</span></h2>
    <div class="tabel-wrap mt"><table>
      <thead><tr><th>No</th><th>Nama</th><th>Waktu presensi</th><th>Status</th><th>Keterangan</th></tr></thead>
      <tbody>
        @foreach ($siswa as $i => $s)
          @php($p = $rekap[$s->id] ?? null)
          @php($st = $p?->status ?? ($tutup ? 'alpa' : ''))
          <tr><td>{{ $i + 1 }}</td><td class="tebal">{{ $s->name }}</td>
            <td class="kecil-redup">{{ $p?->waktu ? $p->waktu->format('H.i') . ($p->oleh_guru ? ' (diisi guru)' : '') : '-' }}</td>
            <td><div class="status-pilih">
              @foreach (['hadir' => 'H', 'izin' => 'I', 'sakit' => 'S', 'alpa' => 'A'] as $val => $hrf)
                <label title="{{ \App\Models\Presensi::STATUS[$val] }}"><input type="radio" name="status[{{ $s->id }}]" value="{{ $val }}" @checked($st === $val)><span class="{{ strtolower($hrf) }}">{{ $hrf }}</span></label>
              @endforeach
            </div></td>
            <td><input type="text" name="ket[{{ $s->id }}]" value="{{ $p?->keterangan }}" maxlength="255" placeholder="opsional" style="min-width:140px"></td></tr>
        @endforeach
      </tbody>
    </table></div>
    <div style="padding:14px 20px"><button class="btn">{!! icon('check', 16) !!}Simpan presensi</button></div>
  </form>
@endif
@endsection
