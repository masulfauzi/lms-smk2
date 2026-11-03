@if (session('ok'))
  <div class="flash ok">{!! icon('check', 18) !!}<div>{{ session('ok') }}</div></div>
@endif
@if (session('error'))
  <div class="flash error">{!! icon('alert', 18) !!}<div>{{ session('error') }}</div></div>
@endif
@if ($errors->any())
  <div class="flash error">{!! icon('alert', 18) !!}<ul>@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
@endif
