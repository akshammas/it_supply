@extends('frontend.layouts.app')
@section('title', 'Blog | '.config('app.name'))

@section('content')
<div class="container py-4">
    <h2 class="mb-4">Blog</h2>

    @if($categories->isNotEmpty())
        <div class="d-flex flex-wrap gap-2 mb-4">
            <a href="{{ route('blog.index') }}" class="btn btn-sm {{ !request('category') ? 'btn-primary' : 'btn-outline-secondary' }}">All</a>
            @foreach($categories as $cat)
                <a href="{{ route('blog.index', ['category' => $cat->slug]) }}" class="btn btn-sm {{ request('category')===$cat->slug ? 'btn-primary' : 'btn-outline-secondary' }}">{{ $cat->name }}</a>
            @endforeach
        </div>
    @endif

    @if($posts->isEmpty())
        <p class="text-muted">No posts published yet.</p>
    @else
        <div class="row">
            @foreach($posts as $post)
                <div class="col-md-4 mb-4">
                    <a href="{{ route('blog.show', $post) }}" class="text-decoration-none text-dark">
                        <div class="card h-100 shadow-sm">
                            @if($post->featured_image)
                                <img src="{{ Storage::url($post->featured_image) }}" class="card-img-top" style="height:180px; object-fit:cover;">
                            @endif
                            <div class="card-body">
                                @if($post->category)<span class="badge bg-light text-dark border mb-2">{{ $post->category->name }}</span>@endif
                                <h5 class="card-title">{{ $post->title }}</h5>
                                <p class="card-text text-muted small">{{ Str::limit($post->excerpt, 100) }}</p>
                                <div class="text-muted small">{{ $post->published_at?->format('d M Y') }}</div>
                            </div>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>
        <div class="mt-3">{{ $posts->links() }}</div>
    @endif
</div>
@endsection
