@php
    $href = url('/blogs/'.$blog->slug);
    $cover = $blog->coverUrl();
@endphp
<article class="itr-blog-card">
    <a class="itr-blog-cover" href="{{ $href }}" tabindex="-1" aria-hidden="true">
        <img src="{{ $cover }}" alt="" width="800" height="480" loading="lazy" decoding="async">
        <span class="itr-blog-cover-tag">{!! icon('pen') !!} Guide</span>
    </a>
    <div class="itr-blog-body">
        <div class="itr-blog-meta">{{ formatDate($blog->published_at) }}@if(!empty($blog->author?->name)) · {{ $blog->author->name }}@endif</div>
        <h3><a href="{{ $href }}">{{ $blog->title }}</a></h3>
        @if(!empty($blog->excerpt))
            <p>{{ $blog->excerpt }}</p>
        @endif
        <a class="itr-link-more" href="{{ $href }}">Read guide {!! icon('arrow-right') !!}</a>
    </div>
</article>
