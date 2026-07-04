@if ($paginator->hasPages())
    <nav class="app-pagination" role="navigation" aria-label="Paginacion">
        <p class="app-pagination__summary">
            Pagina {{ $paginator->currentPage() }}
        </p>

        <div class="app-pagination__links">
            @if ($paginator->onFirstPage())
                <span class="app-pagination__item is-disabled" aria-disabled="true">Anterior</span>
            @else
                <a class="app-pagination__item" href="{{ $paginator->previousPageUrl() }}" rel="prev">Anterior</a>
            @endif

            @if ($paginator->hasMorePages())
                <a class="app-pagination__item" href="{{ $paginator->nextPageUrl() }}" rel="next">Siguiente</a>
            @else
                <span class="app-pagination__item is-disabled" aria-disabled="true">Siguiente</span>
            @endif
        </div>
    </nav>
@endif
