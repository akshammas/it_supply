@extends('frontend.layouts.app')
@section('title', 'Blog | '.config('app.name'))

@section('content')

{{-- Header --}}
<section class="blog-hero py-5">
    <div class="container py-3">
        <div class="section-eyebrow mb-2">Our Blog</div>
        <h1 class="fw-bold display-6 mb-2" style="letter-spacing:-.02em;">Insights, guides &amp; updates</h1>
        <p class="lead text-muted mb-4">Practical advice and news on enterprise IT products and solutions.</p>

        @if($categories->isNotEmpty())
            <div class="blog-filter">
                <a href="{{ route('blog.index') }}" class="{{ !request('category') ? 'active' : '' }}">All</a>
                @foreach($categories as $cat)
                    <a href="{{ route('blog.index', ['category' => $cat->slug]) }}"
                       class="{{ request('category') === $cat->slug ? 'active' : '' }}">{{ $cat->name }}</a>
                @endforeach
            </div>
        @endif
    </div>
</section>

<section class="container py-5">
    @if($posts->isEmpty())
        <div class="text-center py-5">
            <i class="bi bi-journal-text" style="font-size:3.5rem; color:var(--brand-red); opacity:.4;"></i>
            <h4 class="mt-3">No posts yet</h4>
            <p class="text-muted">Please check back soon.</p>
            @if(request('category'))
                <a href="{{ route('blog.index') }}" class="btn btn-outline-brand">View all posts</a>
            @endif
        </div>
    @else
        @php
            $items    = $posts->getCollection();
            $featured = ($posts->onFirstPage() && $items->count() > 1) ? $items->first() : null;
            $rest     = $featured ? $items->slice(1) : $items;
        @endphp

        {{-- Latest post, large --}}
        @if($featured)
            @php $fMinutes = max(1, (int) ceil(str_word_count(strip_tags((string) $featured->content)) / 200)); @endphp
            <a href="{{ route('blog.show', $featured) }}" class="blog-featured mb-5">
                <div class="blog-thumb">
                    @if($featured->featured_image)
                        <img src="{{ Storage::url($featured->featured_image) }}" alt="{{ $featured->title }}">
                    @else
                        <div class="blog-thumb-placeholder"><i class="bi bi-journal-text"></i></div>
                    @endif
                    @if($featured->category)<span class="blog-cat">{{ $featured->category->name }}</span>@endif
                </div>
                <div class="blog-card-body">
                    <span class="blog-flag"><i class="bi bi-stars"></i> Latest post</span>
                    <div class="blog-meta">
                        @if($featured->published_at)<span><i class="bi bi-calendar3"></i>{{ $featured->published_at->format('d M Y') }}</span>@endif
                        <span><i class="bi bi-clock"></i>{{ $fMinutes }} min read</span>
                    </div>
                    <h2 class="blog-title">{{ $featured->title }}</h2>
                    @if($featured->excerpt)<p class="blog-excerpt">{{ $featured->excerpt }}</p>@endif
                    <span class="blog-more">Read article <i class="bi bi-arrow-right"></i></span>
                </div>
            </a>
        @endif

        {{-- Grid --}}
        @if($rest->isNotEmpty())
            <div class="row g-4">
                @foreach($rest as $post)
                    <div class="col-md-6 col-lg-4">
                        @include('frontend.partials.blog-card', ['post' => $post])
                    </div>
                @endforeach
            </div>
        @endif

        <div class="mt-5 d-flex justify-content-center">{{ $posts->withQueryString()->links() }}</div>
    @endif
</section>
@endsection