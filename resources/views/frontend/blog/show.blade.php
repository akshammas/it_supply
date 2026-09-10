@extends('frontend.layouts.app')
@section('title', $post->meta_title ?? $post->title.' | '.config('app.name'))
@section('meta_description', $post->meta_description ?? Str::limit(strip_tags($post->excerpt), 150))

@section('content')
<div class="container py-4" style="max-width:800px;">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('blog.index') }}">Blog</a></li>
            <li class="breadcrumb-item active">{{ $post->title }}</li>
        </ol>
    </nav>

    @if($post->category)<span class="badge bg-light text-dark border mb-2">{{ $post->category->name }}</span>@endif
    <h1>{{ $post->title }}</h1>
    <div class="text-muted small mb-4">
        {{ $post->published_at?->format('d M Y') }}
        @if($post->author) &middot; by {{ $post->author->name }} @endif
    </div>

    @if($post->featured_image)
        <img src="{{ Storage::url($post->featured_image) }}" class="img-fluid rounded mb-4">
    @endif

    <div>{!! nl2br(e($post->content)) !!}</div>

    @if($related->isNotEmpty())
        <hr class="my-5">
        <h4 class="mb-3">More Posts</h4>
        <div class="row">
            @foreach($related as $relatedPost)
                <div class="col-md-4 mb-3">
                    <a href="{{ route('blog.show', $relatedPost) }}" class="text-decoration-none text-dark">
                        <div class="card h-100 shadow-sm">
                            <div class="card-body">
                                <h6 class="card-title">{{ $relatedPost->title }}</h6>
                            </div>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
