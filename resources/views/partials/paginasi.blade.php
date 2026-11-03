@if ($paginator->hasPages())
<nav class="paginasi" aria-label="Halaman">
  @if ($paginator->onFirstPage())<span class="mati">&lsaquo;</span>@else<a href="{{ $paginator->previousPageUrl() }}" rel="prev">&lsaquo;</a>@endif
  @foreach ($elements as $el)
    @if (is_string($el))<span class="mati">{{ $el }}</span>@endif
    @if (is_array($el))
      @foreach ($el as $page => $url)
        @if ($page == $paginator->currentPage())<span class="on">{{ $page }}</span>@else<a href="{{ $url }}">{{ $page }}</a>@endif
      @endforeach
    @endif
  @endforeach
  @if ($paginator->hasMorePages())<a href="{{ $paginator->nextPageUrl() }}" rel="next">&rsaquo;</a>@else<span class="mati">&rsaquo;</span>@endif
</nav>
@endif
