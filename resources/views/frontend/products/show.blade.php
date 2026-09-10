@extends('frontend.layouts.app')
@section('title', $product->meta_title ?? $product->name.' | '.config('app.name'))
@section('meta_description', $product->meta_description ?? Str::limit(strip_tags($product->short_description), 150))

@section('content')
<div class="container py-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
            @if($product->category)
                <li class="breadcrumb-item"><a href="{{ route('categories.show', $product->category) }}">{{ $product->category->name }}</a></li>
            @endif
            <li class="breadcrumb-item active">{{ $product->name }}</li>
        </ol>
    </nav>

    <div class="row">
        {{-- IMAGE GALLERY --}}
        <div class="col-lg-5 mb-4">
            @if($product->images->isNotEmpty())
                <div id="productGallery" class="carousel slide border rounded mb-2" data-bs-ride="false">
                    <div class="carousel-inner">
                        @foreach($product->images as $image)
                            <div class="carousel-item {{ $loop->first ? 'active' : '' }}">
                                <img src="{{ Storage::url($image->image) }}" class="d-block w-100" style="height:360px; object-fit:contain;" alt="{{ $product->name }}">
                            </div>
                        @endforeach
                    </div>
                    @if($product->images->count() > 1)
                        <button class="carousel-control-prev" type="button" data-bs-target="#productGallery" data-bs-slide="prev">
                            <span class="carousel-control-prev-icon"></span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#productGallery" data-bs-slide="next">
                            <span class="carousel-control-next-icon"></span>
                        </button>
                    @endif
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    @foreach($product->images as $image)
                        <img src="{{ Storage::url($image->image) }}" class="border rounded" style="width:60px; height:60px; object-fit:contain; cursor:pointer;"
                             onclick="var c = bootstrap.Carousel.getOrCreateInstance(document.getElementById('productGallery')); c.to({{ $loop->index }})">
                    @endforeach
                </div>
            @else
                <div class="border rounded bg-light d-flex align-items-center justify-content-center" style="height:360px;">
                    <i class="bi bi-image text-muted fs-1"></i>
                </div>
            @endif
        </div>

        {{-- SUMMARY / ACTIONS --}}
        <div class="col-lg-7">
            @if($product->brand)
                <a href="{{ route('brands.show', $product->brand) }}" class="text-decoration-none text-muted small text-uppercase">{{ $product->brand->name }}</a>
            @endif
            <h1 class="h3">{{ $product->name }}</h1>
            <div class="text-muted small mb-3">@if($product->sku) SKU: {{ $product->sku }} @endif @if($product->model_number) &middot; Model: {{ $product->model_number }} @endif</div>

            <div class="mb-3">
                @if($product->price_type === 'on_request')
                    <span class="fs-4 text-muted">Price on Request</span>
                @else
                    <span class="fs-4 fw-bold">AED {{ number_format($product->sale_price ?? $product->price, 2) }}</span>
                    @if($product->price_type === 'sale' && $product->sale_price)
                        <del class="text-muted ms-2">AED {{ number_format($product->price, 2) }}</del>
                    @endif
                @endif
            </div>

            <div class="mb-3 {{ $product->stock_status === 'in_stock' ? 'text-success' : 'text-danger' }}">
                <i class="bi bi-check-circle-fill"></i> {{ str_replace('_', ' ', ucfirst($product->stock_status)) }}
            </div>

            @if($product->short_description)
                <p>{{ $product->short_description }}</p>
            @endif

            <div class="d-flex flex-wrap gap-2 mb-4">
                <a href="https://wa.me/{{ \App\Models\Setting::get('whatsapp_number', '971500000000') }}?text={{ urlencode($product->whatsappMessage()) }}"
                   target="_blank" class="btn btn-success">
                    <i class="bi bi-whatsapp me-1"></i> WhatsApp Enquiry
                </a>
                <a href="{{ route('quote-request.create') }}" class="btn btn-primary" onclick="document.getElementById('quickAddForm').submit(); return false;">
                    <i class="bi bi-file-earmark-text me-1"></i> Request Quote
                </a>
                <form method="POST" action="{{ route('enquiry.add', $product) }}" class="d-inline-flex align-items-center gap-2">
                    @csrf
                    <input type="number" name="quantity" value="1" min="1" class="form-control form-control-sm" style="width:70px">
                    <button type="submit" class="btn btn-outline-primary">
                        <i class="bi bi-cart-plus me-1"></i> Add to Enquiry
                    </button>
                </form>
                <form id="quickAddForm" method="POST" action="{{ route('enquiry.add', $product) }}" class="d-none">
                    @csrf
                    <input type="hidden" name="quantity" value="1">
                    <input type="hidden" name="redirect_to_quote" value="1">
                </form>
            </div>

            @if($product->variants->isNotEmpty())
                <div class="card mb-4">
                    <div class="card-header bg-white fw-semibold">Available Configurations</div>
                    <ul class="list-group list-group-flush">
                        @foreach($product->variants as $variant)
                            <li class="list-group-item d-flex justify-content-between">
                                <span>{{ $variant->name }}</span>
                                <span class="fw-semibold">
                                    @if($variant->sale_price ?? $variant->price)
                                        AED {{ number_format($variant->sale_price ?? $variant->price, 2) }}
                                    @else
                                        On Request
                                    @endif
                                </span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
    </div>

    {{-- OVERVIEW / DESCRIPTION --}}
    @if($product->description)
    <div class="row mt-4">
        <div class="col-lg-9">
            <h4>Overview</h4>
            <div>{!! nl2br(e($product->description)) !!}</div>
        </div>
    </div>
    @endif

    {{-- SPECIFICATIONS --}}
    @if($specGroups->isNotEmpty())
    <div class="row mt-4">
        <div class="col-lg-9">
            <h4>Specifications</h4>
            @foreach($specGroups as $groupName => $specs)
                <h6 class="text-muted text-uppercase small mt-3">{{ $groupName }}</h6>
                <table class="table table-sm table-striped">
                    <tbody>
                        @foreach($specs as $ps)
                            <tr>
                                <th style="width:40%">{{ $ps->specification->name }}</th>
                                <td>{{ $ps->value }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endforeach
        </div>
    </div>
    @endif

    {{-- RELATED PRODUCTS --}}
    @if($related->isNotEmpty())
    <div class="mt-5">
        <h4 class="mb-3">Related Products</h4>
        <div class="row">
            @foreach($related as $relatedProduct)
                @include('frontend.partials.product-card', ['product' => $relatedProduct])
            @endforeach
        </div>
    </div>
    @endif
</div>

{{-- Mobile sticky action bar (section 53) --}}
<div class="d-md-none fixed-bottom bg-white border-top p-2 d-flex gap-2">
    <a href="https://wa.me/{{ \App\Models\Setting::get('whatsapp_number', '971500000000') }}?text={{ urlencode($product->whatsappMessage()) }}"
       target="_blank" class="btn btn-success flex-fill">WhatsApp</a>
    <button type="button" class="btn btn-primary flex-fill" onclick="document.getElementById('quickAddForm').submit()">Request Quote</button>
</div>
@endsection
