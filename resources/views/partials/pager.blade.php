@php
    $paginator = $paginator ?? null;
@endphp
@if($paginator && method_exists($paginator, 'total') && $paginator->total() > 0)
<div class="itr-pager">
    <div class="itr-pager-meta">
        Showing
        <strong>{{ $paginator->firstItem() }}–{{ $paginator->lastItem() }}</strong>
        of <strong>{{ $paginator->total() }}</strong>
        <span class="itr-pager-per">· {{ $paginator->perPage() }} per page</span>
    </div>
    {{ $paginator->onEachSide(1)->links('vendor.pagination.itr') }}
</div>
@endif
