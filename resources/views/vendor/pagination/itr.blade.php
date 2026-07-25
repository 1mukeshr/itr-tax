@if ($paginator->hasPages())
    <nav class="itr-pagination" role="navigation" aria-label="Pagination">
        @if ($paginator->onFirstPage())
            <span class="itr-page-btn is-disabled" aria-disabled="true">← Prev</span>
        @else
            <a class="itr-page-btn" href="{{ $paginator->previousPageUrl() }}" rel="prev">← Prev</a>
        @endif

        <ul class="itr-page-list">
            @foreach ($elements as $element)
                @if (is_string($element))
                    <li><span class="itr-page-ellipsis">{{ $element }}</span></li>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <li><span class="itr-page-num is-active" aria-current="page">{{ $page }}</span></li>
                        @else
                            <li><a class="itr-page-num" href="{{ $url }}">{{ $page }}</a></li>
                        @endif
                    @endforeach
                @endif
            @endforeach
        </ul>

        @if ($paginator->hasMorePages())
            <a class="itr-page-btn" href="{{ $paginator->nextPageUrl() }}" rel="next">Next →</a>
        @else
            <span class="itr-page-btn is-disabled" aria-disabled="true">Next →</span>
        @endif
    </nav>
@endif
