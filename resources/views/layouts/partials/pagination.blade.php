@php
    $start = max(1, $paginator->currentPage() - 2);
    $end = min($paginator->lastPage(), $paginator->currentPage() + 2);
@endphp

@if ($paginator->lastPage() > 1)
    <nav aria-label="Page navigation example">
        <ul class="pagination pagination-primary mb-0">
            <li class="page-item {{ $paginator->onFirstPage() ? 'disabled' : '' }}">
                @if ($paginator->onFirstPage())
                    <span class="page-link" aria-hidden="true">
                        <i class="bi bi-chevron-left"></i>
                    </span>
                @else
                    <a class="page-link" href="{{ $paginator->previousPageUrl() }}" aria-label="Previous">
                        <span aria-hidden="true"><i class="bi bi-chevron-left"></i></span>
                    </a>
                @endif
            </li>

            @for ($page = $start; $page <= $end; $page++)
                <li class="page-item {{ $page === $paginator->currentPage() ? 'active' : '' }}">
                    <a class="page-link" href="{{ $paginator->url($page) }}">{{ $page }}</a>
                </li>
            @endfor

            <li class="page-item {{ $paginator->hasMorePages() ? '' : 'disabled' }}">
                @if ($paginator->hasMorePages())
                    <a class="page-link" href="{{ $paginator->nextPageUrl() }}" aria-label="Next">
                        <span aria-hidden="true"><i class="bi bi-chevron-right"></i></span>
                    </a>
                @else
                    <span class="page-link" aria-hidden="true">
                        <i class="bi bi-chevron-right"></i>
                    </span>
                @endif
            </li>
        </ul>
    </nav>
@endif
