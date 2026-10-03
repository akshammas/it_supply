@extends('frontend.layouts.app')
@section('title', config('app.name').' | Enterprise IT Products & Solutions in UAE')

@section('content')

    {{-- HERO --}}
     @if($heroBanners->isNotEmpty())
        <div id="heroCarousel" class="carousel slide carousel-fade" data-bs-ride="carousel" data-bs-interval="5000">
            @if($heroBanners->count() > 1)
                <div class="carousel-indicators">
                    @foreach($heroBanners as $banner)
                        <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="{{ $loop->index }}" class="{{ $loop->first ? 'active' : '' }}"></button>
                    @endforeach
                </div>
            @endif
            <div class="carousel-inner">
                @foreach($heroBanners as $banner)
                    <div class="carousel-item {{ $loop->first ? 'active' : '' }}">
                        <div class="position-relative overflow-hidden text-white d-flex align-items-center" style="height:420px; background:#111;">
                            <div class="hero-bg" style="background-image:url('{{ Storage::url($banner->image) }}');"></div>
                            <div class="container position-relative text-center py-4">
                                <h1 class="display-5 fw-bold hero-anim">{{ $banner->title ?: \App\Models\Setting::get('hero_title', 'Enterprise IT Products & Solutions') }}</h1>
                                <p class="lead text-white-50 hero-anim">{{ $banner->subtitle ?: \App\Models\Setting::get('hero_subtitle', 'For Businesses Across UAE') }}</p>
                                <div class="hero-anim">
                                    <a href="{{ route('products.index') }}" class="btn btn-brand btn-lg me-2">Explore Products</a>
                                    <a href="{{ $banner->link_url ?: route('quote-request.create') }}" class="btn btn-outline-light btn-lg">{{ $banner->button_text ?: 'Request a Quote' }}</a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @else
        <section style="background:var(--ink);" class="text-white py-5">
            <div class="container text-center py-4">
                <h1 class="display-5 fw-bold">{{ \App\Models\Setting::get('hero_title', 'Enterprise IT Products & Solutions') }}</h1>
                <p class="lead text-white-50">{{ \App\Models\Setting::get('hero_subtitle', 'For Businesses Across UAE') }}</p>
                <a href="{{ route('products.index') }}" class="btn btn-brand btn-lg me-2">Explore Products</a>
                <a href="{{ route('quote-request.create') }}" class="btn btn-outline-light btn-lg">Request a Quote</a>
            </div>
        </section>
    @endif

    {{-- PROMO BANNERS --}}
    @if($promoBanners->isNotEmpty())
        <section class="container py-4">
            <div class="row justify-content-center">
                @foreach($promoBanners as $banner)
                    @php
                        // Real size of the uploaded image (falls back to 2:1 if it can't be read)
                        $file = Storage::disk('public')->path($banner->image);
                        $size = is_file($file) ? @getimagesize($file) : false;
                        $w = $size[0] ?? 800;
                        $h = $size[1] ?? 400;
                    @endphp
                    <div class="col-12 col-md-{{ 12 / min($promoBanners->count(), 3) }} mb-3">
                        <a href="{{ $banner->link_url ?: '#' }}"
                        class="d-block position-relative overflow-hidden rounded text-decoration-none">
                            <img src="{{ Storage::url($banner->image) }}"
                                alt="{{ $banner->title ?? 'Promotion' }}"
                                class="img-fluid w-100 d-block"
                                width="{{ $w }}" height="{{ $h }}"
                                loading="lazy" decoding="async"
                                style="aspect-ratio: {{ $w }} / {{ $h }};">
                            @if($banner->title)
                                <div class="position-absolute bottom-0 start-0 w-100 p-3 text-white"
                                    style="background:linear-gradient(transparent, rgba(0,0,0,.65));">
                                    <div class="fw-semibold">{{ $banner->title }}</div>
                                </div>
                            @endif
                        </a>
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    {{-- TOP BRANDS — grid, max 5 per row --}}
    @if($featuredBrands->isNotEmpty())
    <section class="py-5" style="background:var(--brand-red-light);">
        <div class="container">
            <div class="section-eyebrow">Partners</div>
            <h2 class="section-title mb-4">Top Brands</h2>
            <div class="row row-cols-2 row-cols-sm-3 row-cols-md-5 g-3">
                @foreach($featuredBrands as $brand)
                    <div class="col">
                        <a href="{{ route('brands.show', $brand) }}" class="brand-tile bg-white w-100" style="height:90px;">
                            @if($brand->logo)
                                <img src="{{ Storage::url($brand->logo) }}" alt="{{ $brand->name }}">
                            @else
                                <span class="fw-semibold small text-center">{{ $brand->name }}</span>
                            @endif
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    {{-- BROWSE CATEGORIES — grid, max 5 per row --}}
    @if($featuredCategories->isNotEmpty())
    <section class="container py-5">
        <div class="section-eyebrow">Browse</div>
        <h2 class="section-title mb-4">Shop by Category</h2>
        <div class="row row-cols-3 row-cols-sm-4 row-cols-md-5 g-3">
            @foreach($featuredCategories as $category)
                <div class="col">
                    <a href="{{ route('categories.show', $category) }}" class="category-chip">
                        <div class="chip-icon">
                            @include('frontend.partials.category-icon', ['category' => $category, 'size' => 32, 'iconClass' => 'fs-5 text-danger'])
                        </div>
                        <div class="chip-label">{{ $category->name }}</div>
                    </a>
                </div>
            @endforeach
        </div>
    </section>
    @endif

    {{-- FEATURED PRODUCTS --}}
    @if($featuredProducts->isNotEmpty())
    <section class="container py-5">
        <div class="section-eyebrow">Top Picks</div>
        <h2 class="section-title mb-4">Featured Products</h2>
        <div class="row">
            @foreach($featuredProducts as $product)
                @include('frontend.partials.product-card', ['product' => $product])
            @endforeach
        </div>
    </section>
    @endif

    

    {{-- TRUST BADGES --}}
    <section class="container py-5">
        <div class="row text-center g-4">
            @foreach([
                ['icon' => 'bi-patch-check', 'label' => 'Genuine Products'],
                ['icon' => 'bi-tags', 'label' => 'Competitive Pricing'],
                ['icon' => 'bi-truck', 'label' => 'UAE Delivery'],
                ['icon' => 'bi-headset', 'label' => 'Technical Support'],
                ['icon' => 'bi-shield-check', 'label' => 'Warranty Support'],
            ] as $item)
                <div class="col-6 col-md-2 col-lg-2 mx-auto">
                    <i class="bi {{ $item['icon'] }} fs-1" style="color:var(--brand-red);"></i>
                    <p class="small mt-2 mb-0 fw-semibold">{{ $item['label'] }}</p>
                </div>
            @endforeach
        </div>
    </section>

    {{-- CTA BAND --}}
    <section class="cta-band text-center py-5">
        <div class="container">
            <h4 class="mb-3 fw-bold">Looking for a specific IT product?</h4>
            <a href="{{ route('quote-request.create') }}" class="btn btn-light btn-lg fw-semibold">Request a Quote</a>
        </div>
    </section>

@endsection