@extends('frontend.layouts.app')
@section('title', config('app.name').' | Enterprise IT Products & Solutions in UAE')

@section('content')

    {{-- HERO — uses a home_hero Banner's image as background if one exists,
         otherwise falls back to a plain dark background. Title/subtitle
         always come from Settings (editable without a banner). --}}
    <section class="text-white py-5 {{ $heroBanner ? '' : 'bg-dark' }}"
        @if($heroBanner) style="background: #111 url('{{ Storage::url($heroBanner->image) }}') center/cover;" @endif>
        <div class="container text-center py-4">
            <h1 class="display-5 fw-bold">{{ \App\Models\Setting::get('hero_title', 'Enterprise IT Products & Solutions') }}</h1>
            <p class="lead text-white-50">{{ \App\Models\Setting::get('hero_subtitle', 'For Businesses Across UAE') }}</p>
            <p class="mb-4">Servers &middot; Networking &middot; Security &middot; POS &middot; Access Control</p>
            <a href="{{ route('products.index') }}" class="btn btn-light btn-lg me-2">Explore Products</a>
            <a href="{{ route('products.index') }}" class="btn btn-outline-light btn-lg">Request a Quote</a>
        </div>
    </section>

    {{-- PROMO BANNERS --}}
    @if($promoBanners->isNotEmpty())
    <section class="container py-4">
        <div class="row">
            @foreach($promoBanners as $banner)
                <div class="col-md-{{ 12 / min($promoBanners->count(), 3) }} mb-3">
                    <a href="{{ $banner->link_url ?: '#' }}" class="d-block position-relative text-decoration-none">
                        <img src="{{ Storage::url($banner->image) }}" class="img-fluid rounded w-100" style="height:160px; object-fit:cover;">
                        @if($banner->title)
                            <div class="position-absolute bottom-0 start-0 p-3 text-white" style="background:linear-gradient(transparent, rgba(0,0,0,.6)); width:100%;">
                                <div class="fw-semibold">{{ $banner->title }}</div>
                                @if($banner->button_text)<span class="small">{{ $banner->button_text }} &rarr;</span>@endif
                            </div>
                        @endif
                    </a>
                </div>
            @endforeach
        </div>
    </section>
    @endif

    {{-- BRANDS — now first, using the card-grid design (image, name, shadow) --}}
    @if($featuredBrands->isNotEmpty())
    <section class="container py-5">
        <h3 class="mb-4">Featured Brands</h3>
        <div class="row">
            @foreach($featuredBrands as $brand)
                <div class="col-6 col-md-3 mb-4">
                    <a href="{{ route('brands.show', $brand) }}" class="text-decoration-none">
                        <div class="card h-100 text-center shadow-sm">
                            @if($brand->logo)
                                <img src="{{ Storage::url($brand->logo) }}" class="card-img-top p-3" style="height:120px; object-fit:contain;" alt="{{ $brand->name }}">
                            @else
                                <div class="bg-light d-flex align-items-center justify-content-center" style="height:120px;">
                                    <i class="bi bi-award fs-2 text-muted"></i>
                                </div>
                            @endif
                            <div class="card-body py-2">
                                <div class="text-dark small fw-semibold">{{ $brand->name }}</div>
                            </div>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>
    </section>
    @endif

    {{-- CATEGORIES — now second, using the simple logo-strip design --}}
    @if($featuredCategories->isNotEmpty())
    <section class="bg-light py-5">
        <div class="container">
            <h3 class="mb-4">Shop by Category</h3>
            <div class="row align-items-center">
                @foreach($featuredCategories as $category)
                    <div class="col-4 col-md-2 mb-4 text-center">
                        <a href="{{ route('categories.show', $category) }}" class="text-decoration-none">
                            @if($category->image)
                                <img src="{{ Storage::url($category->image) }}" class="img-fluid" style="max-height:50px;" alt="{{ $category->name }}">
                            @else
                                <i class="bi bi-box2 fs-3 text-muted d-block mb-1"></i>
                            @endif
                            <div class="small fw-semibold text-dark mt-2">{{ $category->name }}</div>
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    {{-- FEATURED PRODUCTS --}}
    @if($featuredProducts->isNotEmpty())
    <section class="container py-5">
        <h3 class="mb-4">Featured Products</h3>
        <div class="row">
            @foreach($featuredProducts as $product)
                @include('frontend.partials.product-card', ['product' => $product])
            @endforeach
        </div>
    </section>
    @endif

    {{-- SOLUTIONS --}}
    @if($solutions->isNotEmpty())
    <section class="bg-light py-5">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3 class="mb-0">Solutions</h3>
                <a href="{{ route('solutions.index') }}" class="small">View all &rarr;</a>
            </div>
            <div class="row">
                @foreach($solutions as $solution)
                    <div class="col-md-4 mb-4">
                        <a href="{{ route('solutions.show', $solution) }}" class="text-decoration-none text-dark">
                            <div class="card h-100 shadow-sm">
                                @if($solution->image)
                                    <img src="{{ Storage::url($solution->image) }}" class="card-img-top" style="height:160px; object-fit:cover;">
                                @endif
                                <div class="card-body">
                                    <h5 class="card-title">{{ $solution->name }}</h5>
                                    <p class="card-text text-muted small">{{ Str::limit($solution->short_description, 100) }}</p>
                                </div>
                            </div>
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    {{-- WHY CHOOSE US --}}
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
                    <i class="bi {{ $item['icon'] }} fs-1 text-primary"></i>
                    <p class="small mt-2 mb-0">{{ $item['label'] }}</p>
                </div>
            @endforeach
        </div>
    </section>

    {{-- CTA --}}
    <section class="bg-dark text-white text-center py-5">
        <div class="container">
            <h4 class="mb-3">Looking for a specific IT product?</h4>
            <a href="{{ route('products.index') }}" class="btn btn-light btn-lg">Request a Quote</a>
        </div>
    </section>

@endsection