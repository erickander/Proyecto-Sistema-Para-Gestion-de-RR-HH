@if ($paginator->hasPages())
    <nav class="app-pagination" role="navigation" aria-label="Paginacion">
        <p class="app-pagination__summary">
            Mostrando {{ $paginator->firstItem() }} a {{ $paginator->lastItem() }} de {{ $paginator->total() }} registros
        </p>

        <div class="app-pagination__links">
            @if ($paginator->onFirstPage())
                <span class="app-pagination__item is-disabled" aria-disabled="true">Anterior</span>
            @else
                <a class="app-pagination__item" href="{{ $paginator->previousPageUrl() }}" rel="prev">Anterior</a>
            @endif

            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="app-pagination__item is-gap" aria-disabled="true">{{ $element }}</span>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span class="app-pagination__item is-active" aria-current="page">{{ $page }}</span>
                        @else
                            <a class="app-pagination__item" href="{{ $url }}">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <a class="app-pagination__item" href="{{ $paginator->nextPageUrl() }}" rel="next">Siguiente</a>
            @else
                <span class="app-pagination__item is-disabled" aria-disabled="true">Siguiente</span>
            @endif
        </div>
    </nav>
@endif
