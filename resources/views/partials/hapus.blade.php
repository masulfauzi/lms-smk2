{{-- Tombol hapus kecil: @include('partials.hapus', ['url' => ..., 'pesan' => ...]) --}}
<form method="post" action="{{ $url }}" data-konfirmasi="{{ $pesan ?? 'Hapus data ini?' }}" style="display:inline">
  @csrf @method('delete')
  <button class="{{ $kelas ?? 'ikon-btn' }}" title="Hapus">{!! icon('trash', 16) !!}{{ $label ?? '' }}</button>
</form>
