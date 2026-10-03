@extends('frontend.layouts.app')
@section('title', $post->meta_title ?? $post->title.' | '.config('app.name'))
@section('meta_description', $post->meta_description ?? Str::limit(strip_tags($post->excerpt), 150))

@section('content')
@php
    $content    = (string) $post->content;
    $minutes    = max(1, (int) ceil(str_word_count(strip_tags($content)) / 200));
    $isHtml     = $content !== strip_tags($content);
    $shareUrl   = rawurlencode(url()->current());
    $shareTitle = rawurlencode($post->title);
@endphp

<div class="read-progress" id="readProgress"></div>

{{-- Header --}}
<section class="post-hero py-5">
    <div class="container">
        <div style="max-width:820px;">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb small mb-3">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('blog.index') }}">Blog</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ Str::limit($post->title, 50) }}</li>
                </ol>
            </nav>

            @if($post->category)
                <a href="{{ route('blog.index', ['category' => $post->category->slug]) }}" class="post-cat">{{ $post->category->name }}</a>
            @endif
            <h1 class="post-title mt-2 mb-3">{{ $post->title }}</h1>
            @if($post->excerpt)<p class="lead text-muted mb-4">{{ $post->excerpt }}</p>@endif

            <div class="post-meta">
                @if($post->author)
                    <span class="post-author">
                        <span class="post-avatar">{{ strtoupper(mb_substr($post->author->name, 0, 1)) }}</span>
                        <strong class="text-dark">{{ $post->author->name }}</strong>
                    </span>
                @endif
                @if($post->published_at)<span><i class="bi bi-calendar3"></i>{{ $post->published_at->format('d M Y') }}</span>@endif
                <span><i class="bi bi-clock"></i>{{ $minutes }} min read</span>
            </div>
        </div>
    </div>
</section>

{{-- Article + sidebar --}}
<section class="container py-5">
    <div class="row g-5">
        <div class="col-lg-8">
            @if($post->featured_image)
                <figure class="post-cover mb-5">
                    <img src="{{ Storage::url($post->featured_image) }}" alt="{{ $post->title }}" fetchpriority="high">
                </figure>
            @endif

            <article class="blog-content">
                @if($isHtml)
                    {!! $content !!}
                @else
                    @foreach(array_filter(preg_split('/\R{2,}/', trim($content))) as $para)
                        <p>{!! nl2br(e($para)) !!}</p>
                    @endforeach
                @endif
            </article>

            <div class="mt-5 pt-4 border-top">
                <a href="{{ route('blog.index') }}" class="post-back"><i class="bi bi-arrow-left me-1"></i> Back to all posts</a>
            </div>
        </div>

        <aside class="col-lg-4">
            <div class="post-side">
                <div class="side-card">
                    <div class="side-title">Share this post</div>
                    <div class="share-row">
                        <a class="share-btn wa" target="_blank" rel="noopener" aria-label="Share on WhatsApp"
                           href="https://wa.me/?text={{ $shareTitle }}%20{{ $shareUrl }}"><i class="bi bi-whatsapp"></i></a>
                        <a class="share-btn li" target="_blank" rel="noopener" aria-label="Share on LinkedIn"
                           href="https://www.linkedin.com/sharing/share-offsite/?url={{ $shareUrl }}"><i class="bi bi-linkedin"></i></a>
                        <a class="share-btn x" target="_blank" rel="noopener" aria-label="Share on X"
                           href="https://twitter.com/intent/tweet?url={{ $shareUrl }}&amp;text={{ $shareTitle }}"><i class="bi bi-twitter-x"></i></a>
                        <a class="share-btn fb" target="_blank" rel="noopener" aria-label="Share on Facebook"
                           href="https://www.facebook.com/sharer/sharer.php?u={{ $shareUrl }}"><i class="bi bi-facebook"></i></a>
                        <button type="button" class="share-btn cp" id="copyLink" aria-label="Copy link"><i class="bi bi-link-45deg"></i></button>
                    </div>
                </div>

                <div class="side-card side-cta">
                    <div class="fw-bold fs-5 mb-1">Need IT products?</div>
                    <p>Tell us what you need and get a quick quotation from our team.</p>
                    <a href="{{ route('quote-request.create') }}" class="btn btn-light w-100">Request a Quote</a>
                </div>
            </div>
        </aside>
    </div>
</section>

{{-- More posts --}}
@if($related->isNotEmpty())
    <section class="py-5" style="background:var(--brand-red-light);">
        <div class="container">
            <div class="section-eyebrow">Keep reading</div>
            <h2 class="section-title mb-4">More posts you may like</h2>
            <div class="row g-4">
                @foreach($related as $relatedPost)
                    <div class="col-md-6 col-lg-4">
                        @include('frontend.partials.blog-card', ['post' => $relatedPost])
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endif
@endsection

@section('scripts')
<script>
    // Reading progress bar
    (function () {
        var bar = document.getElementById('readProgress');
        if (!bar) return;
        function update() {
            var h = document.documentElement;
            var max = h.scrollHeight - h.clientHeight;
            bar.style.width = (max > 0 ? (h.scrollTop / max) * 100 : 0) + '%';
        }
        window.addEventListener('scroll', update, { passive: true });
        update();
    })();

    // Copy link button
    (function () {
        var btn = document.getElementById('copyLink');
        if (!btn) return;
        btn.addEventListener('click', function () {
            var icon = btn.querySelector('i');
            function done() {
                icon.className = 'bi bi-check2';
                setTimeout(function () { icon.className = 'bi bi-link-45deg'; }, 1600);
            }
            if (navigator.clipboard) {
                navigator.clipboard.writeText(window.location.href).then(done);
            } else {
                var t = document.createElement('input');
                t.value = window.location.href;
                document.body.appendChild(t); t.select();
                document.execCommand('copy');
                document.body.removeChild(t); done();
            }
        });
    })();
</script>
@endsection