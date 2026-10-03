@php
    $minutes = max(1, (int) ceil(str_word_count(strip_tags((string) $post->content)) / 200));
@endphp
<a href="{{ route('blog.show', $post) }}" class="blog-card">
    <div class="blog-thumb">
        @if($post->featured_image)
            <img src="{{ Storage::url($post->featured_image) }}" alt="{{ $post->title }}" loading="lazy">
        @else
            <div class="blog-thumb-placeholder"><i class="bi bi-journal-text"></i></div>
        @endif
        @if($post->category)<span class="blog-cat">{{ $post->category->name }}</span>@endif
    </div>
    <div class="blog-card-body">
        <div class="blog-meta">
            @if($post->published_at)<span><i class="bi bi-calendar3"></i>{{ $post->published_at->format('d M Y') }}</span>@endif
            <span><i class="bi bi-clock"></i>{{ $minutes }} min read</span>
        </div>
        <h3 class="blog-title">{{ $post->title }}</h3>
        @if($post->excerpt)<p class="blog-excerpt">{{ $post->excerpt }}</p>@endif
        <span class="blog-more">Read more <i class="bi bi-arrow-right"></i></span>
    </div>
</a>