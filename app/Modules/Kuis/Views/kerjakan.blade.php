@extends('layouts.app')
@section('title', $kuis->judul)
@section('content')
<div class="kepala">
  <div><div class="remah">{{ $ruang->mapel->nama }} · {{ $ruang->kelas->nama }}</div><h1><span class="sorot">{{ $kuis->judul }}</span></h1></div>
  <div style="text-align:right"><div class="kecil-redup">Sisa waktu</div><div class="timer" id="timer">--:--</div><div class="simpan-status" id="status">Jawaban tersimpan otomatis</div></div>
</div>
<div class="grid g-utama">
  <div class="kartu">
    @foreach ($daftar as $i => $s)
      @php($jwb = $p->jawaban[$s->id] ?? null)
      <div class="soal-kartu {{ $i === 0 ? 'aktif' : '' }}" data-no="{{ $i }}" data-id="{{ $s->id }}">
        <div class="remah">Soal {{ $i + 1 }} dari {{ $daftar->count() }} · {{ $s->jenis === 'pg' ? 'Pilihan ganda' : 'Esai' }}</div>
        <div class="soal-tanya">{{ $s->pertanyaan }}</div>
        @if ($s->jenis === 'pg')
          @foreach ($p->urutan_soal['opsi'][$s->id] ?? array_keys($s->opsi) as $j => $h)
            <label class="opsi"><input type="radio" name="s{{ $s->id }}" value="{{ $h }}" @checked($jwb === $h)>
              <span class="huruf">{{ chr(65 + $j) }}</span><span>{{ $s->opsi[$h] }}</span></label>
          @endforeach
        @else
          <textarea name="s{{ $s->id }}" class="besar" placeholder="Tulis jawabanmu...">{{ $jwb }}</textarea>
        @endif
        <div style="display:flex;justify-content:space-between;margin-top:16px;gap:8px">
          <button type="button" class="btn garis" data-ke="{{ $i - 1 }}" @disabled($i === 0)>{!! icon('back', 16) !!}Sebelumnya</button>
          @if ($i < $daftar->count() - 1)<button type="button" class="btn" data-ke="{{ $i + 1 }}">Berikutnya{!! icon('arrow', 16) !!}</button>@endif
        </div>
      </div>
    @endforeach
  </div>
  <div class="kartu">
    <h2>{!! icon('grid', 18) !!}Nomor soal</h2>
    <div class="soal-nav" id="nav">
      @foreach ($daftar as $i => $s)
        @php($jwb = $p->jawaban[$s->id] ?? null)
        <button type="button" data-ke="{{ $i }}" class="{{ $i === 0 ? 'aktif' : '' }} {{ $jwb !== null && $jwb !== '' ? 'terjawab' : '' }}">{{ $i + 1 }}</button>
      @endforeach
    </div>
    <p class="kecil-redup mt"><span id="terjawab">0</span> dari {{ $daftar->count() }} soal terjawab.</p>
    <form method="post" action="{{ route('kuis.kumpul', [$ruang, $kuis]) }}" id="form-kumpul">
      @csrf<button class="btn stabilo blok" id="tombol-kumpul">{!! icon('check', 16) !!}Kumpulkan jawaban</button></form>
  </div>
</div>
@push('script')
<script>
(function () {
  var batas = new Date(@json($p->batas_at->toIso8601String())).getTime();
  var selisihJam = Date.now() - @json(now()->getTimestampMs()); // koreksi jam perangkat siswa
  var urlJawab = @json(route('kuis.jawab', [$ruang, $kuis]));
  var token = document.querySelector('meta[name=csrf-token]').content;
  var kartu = document.querySelectorAll('.soal-kartu'), nav = document.querySelectorAll('#nav button');
  var status = document.getElementById('status'), form = document.getElementById('form-kumpul');
  var antre = {}, timerKetik = {};

  function ke(n) {
    if (n < 0 || n >= kartu.length) return;
    kartu.forEach(function (k, i) { k.classList.toggle('aktif', i === n); });
    nav.forEach(function (b, i) { b.classList.toggle('aktif', i === n); });
    window.scrollTo({ top: 0, behavior: 'smooth' });
  }
  document.addEventListener('click', function (e) {
    var b = e.target.closest('[data-ke]');
    if (b) ke(parseInt(b.dataset.ke, 10));
  });

  function hitung() {
    var n = 0;
    kartu.forEach(function (k, i) {
      var r = k.querySelector('input:checked'), t = k.querySelector('textarea');
      var isi = r || (t && t.value.trim() !== '');
      nav[i].classList.toggle('terjawab', !!isi);
      if (isi) n++;
    });
    document.getElementById('terjawab').textContent = n;
    return n;
  }

  function kirim(id, nilai) {
    status.textContent = 'Menyimpan...';
    fetch(urlJawab, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': token },
      body: JSON.stringify({ soal_id: id, jawaban: nilai })
    }).then(function (r) {
      if (r.status === 409) { form.submit(); return null; }
      return r.json();
    }).then(function (d) {
      if (!d) return;
      delete antre[id];
      status.textContent = d.ok ? 'Tersimpan ' + d.tersimpan : 'Gagal menyimpan';
    }).catch(function () {
      antre[id] = nilai;
      status.textContent = 'Koneksi terputus, akan dicoba lagi...';
    });
  }
  // Coba kirim ulang jawaban yang gagal (sinyal hilang)
  setInterval(function () { Object.keys(antre).forEach(function (id) { kirim(parseInt(id, 10), antre[id]); }); }, 8000);

  kartu.forEach(function (k) {
    var id = parseInt(k.dataset.id, 10);
    k.addEventListener('change', function (e) { if (e.target.type === 'radio') { hitung(); kirim(id, e.target.value); } });
    k.addEventListener('input', function (e) {
      if (e.target.tagName !== 'TEXTAREA') return;
      hitung();
      clearTimeout(timerKetik[id]);
      timerKetik[id] = setTimeout(function () { kirim(id, e.target.value); }, 900);
    });
  });

  form.addEventListener('submit', function (e) {
    if (form.dataset.paksa) return;
    var sisa = kartu.length - hitung();
    if (!confirm(sisa ? 'Masih ada ' + sisa + ' soal kosong. Tetap kumpulkan?' : 'Kumpulkan jawaban sekarang?')) e.preventDefault();
  });

  var el = document.getElementById('timer');
  function detak() {
    var s = Math.max(0, Math.floor((batas - (Date.now() - selisihJam)) / 1000));
    var j = Math.floor(s / 3600), m = Math.floor(s % 3600 / 60), d = s % 60;
    el.textContent = (j ? j + ':' : '') + String(m).padStart(2, '0') + ':' + String(d).padStart(2, '0');
    el.classList.toggle('kritis', s <= 60);
    if (s === 0) { form.dataset.paksa = 1; status.textContent = 'Waktu habis, mengumpulkan...'; form.submit(); return; }
    setTimeout(detak, 1000);
  }
  hitung(); detak();
})();
</script>
@endpush
@endsection
