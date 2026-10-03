@extends('frontend.layouts.app')
@section('title', $category->meta_title ?? $category->name.' | '.config('app.name'))
@section('meta_description', $category->meta_description ?? Str::limit(strip_tags($category->description), 150))

@section('content')
<div class="container py-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('products.index') }}">Categories</a></li>
            <li class="breadcrumb-item active">{{ $category->name }}</li>
        </ol>
    </nav>

    <h2>{{ $category->name }}</h2>
        {{-- CATEGORY TOP BANNER(S) --}}
    @if($topBanners->isNotEmpty())
        @php
            // Use the first banner's real size for all slides so the height never jumps
            $file = Storage::disk('public')->path($topBanners->first()->image);
            $size = is_file($file) ? @getimagesize($file) : false;
            $w = $size[0] ?? 1240;
            $h = $size[1] ?? 300;
        @endphp
        <div id="categoryTopCarousel" class="carousel slide carousel-fade rounded-4 overflow-hidden mb-4"
             data-bs-ride="carousel" data-bs-interval="5000">
            @if($topBanners->count() > 1)
                <div class="carousel-indicators">
                    @foreach($topBanners as $banner)
                        <button type="button" data-bs-target="#categoryTopCarousel" data-bs-slide-to="{{ $loop->index }}"
                                class="{{ $loop->first ? 'active' : '' }}" aria-label="Slide {{ $loop->iteration }}"></button>
                    @endforeach
                </div>
            @endif
            <div class="carousel-inner">
                @foreach($topBanners as $banner)
                    @php $tag = $banner->link_url ? 'a' : 'div'; @endphp
                    <div class="carousel-item {{ $loop->first ? 'active' : '' }}">
                        <{{ $tag }} @if($banner->link_url) href="{{ $banner->link_url }}" @endif
                                    class="d-block position-relative text-decoration-none">
                            <img src="{{ Storage::url($banner->image) }}"
                                 alt="{{ $banner->title ?? $category->name }}"
                                 class="img-fluid w-100 d-block"
                                 width="{{ $w }}" height="{{ $h }}"
                                 decoding="async"
                                 @if(!$loop->first) loading="lazy" @endif
                                 style="aspect-ratio: {{ $w }} / {{ $h }}; object-fit: cover;">
                            @if($banner->title)
                                <div class="position-absolute bottom-0 start-0 w-100 p-3 p-md-4 text-white d-none d-md-block"
                                     style="background:linear-gradient(transparent, rgba(0,0,0,.65));">
                                    <div class="fw-bold fs-5">{{ $banner->title }}</div>
                                    @if($banner->subtitle)<div class="small text-white-50">{{ $banner->subtitle }}</div>@endif
                                </div>
                            @endif
                        </{{ $tag }}>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
    @if($category->description)
        <p class="text-muted">{{ $category->description }}</p>
    @endif

    @if($children->isNotEmpty())
        <div class="d-flex flex-wrap gap-2 mb-4">
            @foreach($children as $child)
                <a href="{{ route('categories.show', $child) }}" class="btn btn-sm btn-outline-secondary">{{ $child->name }}</a>
            @endforeach
        </div>
    @endif

    @if($products->count())
        <div class="row">
            @foreach($products as $product)
                @include('frontend.partials.product-card', ['product' => $product])
            @endforeach
        </div>
        <div class="mt-3">{{ $products->links() }}</div>
    @else
        <p class="text-muted py-5 text-center">No products in this category yet.</p>
    @endif
</div>
@endsection
