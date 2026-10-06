@extends('frontend.layouts.app')
@section('title', $brand->meta_title ?? $brand->name.' | '.config('app.name'))
@section('meta_description', $brand->meta_description ?? Str::limit(strip_tags($brand->description), 150))

@section('content')
<div class="container py-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('products.index') }}">Brands</a></li>
            <li class="breadcrumb-item active">{{ $brand->name }}</li>
        </ol>
    </nav>
    @include('frontend.partials.top-banners', ['banners' => $topBanners, 'carouselId' => 'brandTopCarousel'])
    <div class="d-flex align-items-center gap-3 mb-3">
        @if($brand->logo)
            <img src="{{ Storage::url($brand->logo) }}" style="max-height:60px;" alt="{{ $brand->name }}">
        @endif
        <h2 class="mb-0">{{ $brand->name }}</h2>
    </div>
    @if($brand->description)
        <p class="text-muted">{{ $brand->description }}</p>
    @endif

    @if($products->count())
        <div class="row">
            @foreach($products as $product)
                @include('frontend.partials.product-card', ['product' => $product])
            @endforeach
        </div>
        <div class="mt-3">{{ $products->links() }}</div>
    @else
        <p class="text-muted py-5 text-center">No products from this brand yet.</p>
    @endif
</div>
@endsection
