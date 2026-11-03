<form class="inline-form" method="get">
  <div class="field"><select name="kelas" onchange="this.form.submit()">@foreach ($kelasList as $k)<option value="{{ $k->id }}" @selected($kelas?->id === $k->id)>{{ $k->nama }}</option>@endforeach</select></div>
  {{ $slot ?? '' }}
</form>
