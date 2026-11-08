@php
    $company   = \App\Models\Setting::get('company_name', config('app.name'));
    $activeCat = request('category') ? $categories->firstWhere('slug', request('category')) : null;

    $pageTitle = ($activeCat ? $activeCat->name . ' | ' : '') . 'IT Blog | ' . config('app.name');
    $pageDesc  = \Illuminate\Support\Str::limit(
        $activeCat
            ? 'Articles about ' . $activeCat->name . ' from ' . $company . ': practical advice and news on enterprise IT products in the UAE.'
            : 'Practical advice and news on enterprise IT products and solutions in the UAE, from ' . $company . '.',
        158
    );

    $query = array_filter([
        'category' => $activeCat?->slug,
        'page'     => $posts->currentPage() > 1 ? $posts->currentPage() : null,
    ]);
    $canonical = url()->current() . ($query ? '?' . http_build_query($query) : '');

    $graph = [
        '@context' => 'https://schema.org',
        '@graph' => [
            [
                '@type' => 'Blog',
                '@id'   => $canonical . '#blog',
                'url'   => $canonical,
                'name'  => $activeCat ? $activeCat->name . ' | ' . $company . ' blog' : $company . ' blog',
                'description' => $pageDesc,
                'publisher' => ['@type' => 'Organization', 'name' => $company, 'url' => url('/')],
                'blogPost' => $posts->getCollection()->map(fn ($p) => array_filter([
                    '@type' => 'BlogPosting',
                    'headline' => $p->title,
                    'url' => route('blog.show', $p),
                    'datePublished' => optional($p->published_at)->toIso8601String(),
                    'image' => $p->featured_image ? url(Storage::url($p->featured_image)) : null,
                ]))->values()->all(),
            ],
            [
                '@type' => 'BreadcrumbList',
                'itemListElement' => [
                    ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => url('/')],
                    ['@type' => 'ListItem', 'position' => 2, 'name' => 'Blog', 'item' => route('blog.index')],
                ],
            ],
        ],
    ];
    $jsonFlags = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG;
@endphp
@extends('frontend.layouts.app')

@section('title', $pageTitle)
@section('meta_description', $pageDesc)

{{-- Needs @stack('head') before </head> in the layout. --}}
@push('head')
<link rel="canonical" href="{{ $canonical }}">
<meta property="og:type" content="website">
<meta property="og:site_name" content="{{ $company }}">
<meta property="og:title" content="{{ $pageTitle }}">
<meta property="og:description" content="{{ $pageDesc }}">
<meta property="og:url" content="{{ $canonical }}">
<meta name="twitter:card" content="summary">
@endpush

@section('content')

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Sora:wght@500;600;700&display=swap">

<script>
    if (!window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        document.documentElement.classList.add('js');
    }
</script>

<div class="blog-page">

    {{-- Header --}}
    <section class="blog-hero">
        <div class="container bl-in">
            <nav class="bl-crumbs" aria-label="Breadcrumb" style="--i:0">
                <a href="{{ url('/') }}">Home</a> / <a href="{{ route('blog.index') }}">Blog</a>@if($activeCat) / {{ $activeCat->name }}@endif
            </nav>
            <h1 style="--i:1">{{ $activeCat ? $activeCat->name : 'Enterprise IT news and advice' }}</h1>
            <p class="bl-lede" style="--i:2">
                @if($activeCat)
                    Articles filed under {{ $activeCat->name }}.
                @else
                    Practical advice and news on enterprise IT products and solutions.
                @endif
            </p>

            @if($categories->isNotEmpty())
                <div class="blog-filter" style="--i:3">
                    <a href="{{ route('blog.index') }}" class="{{ !request('category') ? 'active' : '' }}">All</a>
                    @foreach($categories as $cat)
                        <a href="{{ route('blog.index', ['category' => $cat->slug]) }}"
                           class="{{ request('category') === $cat->slug ? 'active' : '' }}">{{ $cat->name }}</a>
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    {{-- Posts --}}
    <section class="container bl-sec">
        @if($posts->isEmpty())
            <div class="bl-empty">
                <i class="bi bi-journal-text"></i>
                <h2 class="h4 mt-3">No posts yet</h2>
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
                <div class="mb-5" data-reveal>
                    <a href="{{ route('blog.show', $featured) }}" class="blog-featured">
                        <div class="blog-thumb">
                            @if($featured->featured_image)
                                <img src="{{ Storage::url($featured->featured_image) }}" alt="{{ $featured->title }}"
                                     width="1000" height="640" fetchpriority="high" decoding="async">
                            @else
                                <div class="blog-thumb-placeholder"><i class="bi bi-journal-text"></i></div>
                            @endif
                            @if($featured->category)<span class="blog-cat">{{ $featured->category->name }}</span>@endif
                        </div>
                        <div class="blog-card-body">
                            <span class="blog-flag">Latest post</span>
                            <div class="blog-meta">
                                @if($featured->published_at)<span><i class="bi bi-calendar3"></i>{{ $featured->published_at->format('d M Y') }}</span>@endif
                                <span><i class="bi bi-clock"></i>{{ $fMinutes }} min read</span>
                            </div>
                            <h2 class="blog-title">{{ $featured->title }}</h2>
                            @if($featured->excerpt)<p class="blog-excerpt">{{ $featured->excerpt }}</p>@endif
                            <span class="blog-more">Read article <i class="bi bi-arrow-right"></i></span>
                        </div>
                    </a>
                </div>
            @endif

            {{-- Grid --}}
            @if($rest->isNotEmpty())
                <div class="row g-4">
                    @foreach($rest as $post)
                        <div class="col-md-6 col-lg-4" data-reveal style="--d: {{ ($loop->index % 3) * .1 }}s">
                            @include('frontend.partials.blog-card', ['post' => $post])
                        </div>
                    @endforeach
                </div>
            @endif

            @if($posts->hasPages())
                <nav class="blog-pager mt-5 d-flex justify-content-center" aria-label="Blog pages">
                    {{ $posts->withQueryString()->links() }}
                </nav>
            @endif
        @endif
    </section>

    {{-- CTA --}}
    <section class="bl-cta">
        <div class="container">
            <div class="row align-items-center g-4">
                <div class="col-lg-8" data-reveal>
                    <h2>Looking for a specific IT product?</h2>
                    <p class="mb-0">Send the model or part number with the quantity you need. Our team replies with pricing and availability.</p>
                </div>
                <div class="col-lg-4 text-lg-end" data-reveal style="--d:.1s">
                    <a href="{{ route('quote-request.create') }}" class="btn btn-brand btn-lg">Request a Quote</a>
                </div>
            </div>
        </div>
    </section>

</div>

<script type="application/ld+json">{!! json_encode($graph, $jsonFlags) !!}</script>

<script>
    (function () {
        var els = document.querySelectorAll('.blog-page [data-reveal]');
        if (!document.documentElement.classList.contains('js') || !('IntersectionObserver' in window)) {
            els.forEach(function (el) { el.classList.add('in'); });
            return;
        }
        var io = new IntersectionObserver(function (entries) {
            entries.forEach(function (e) {
                if (e.isIntersecting) { e.target.classList.add('in'); io.unobserve(e.target); }
            });
        }, { threshold: 0.12, rootMargin: '0px 0px -6% 0px' });
        els.forEach(function (el) { io.observe(el); });
    })();
</script>

@endsection