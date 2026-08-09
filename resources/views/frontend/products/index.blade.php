@extends('frontend.layouts.app')
@section('title', ($heading ?? 'Products').' | '.config('app.name'))

@section('content')
<div class="container py-4">
    <h3 class="mb-4">{{ $heading ?? 'All Products' }}</h3>

    <div class="row">
        <div class="col-lg-3 mb-4">
            <form method="GET" class="card shadow-sm">
                <div class="card-body">
                    <input type="hidden" name="q" value="{{ request('q') }}">

                    <label class="form-label small text-uppercase text-muted">Brand</label>
                    <select name="brand" class="form-select mb-3" onchange="this.form.submit()">
                        <option value="">All Brands</option>
                        @foreach($brands as $brand)
                            <option value="{{ $brand->slug }}" @selected(request('brand') === $brand->slug)>{{ $brand->name }}</option>
                        @endforeach
                    </select>

                    <label class="form-label small text-uppercase text-muted">Category</label>
                    <select name="category" class="form-select mb-3" onchange="this.form.submit()">
                        <option value="">All Categories</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->slug }}" @selected(request('category') === $category->slug)>{{ $category->name }}</option>
                        @endforeach
                    </select>

                    <label class="form-label small text-uppercase text-muted">Availability</label>
                    <select name="stock_status" class="form-select mb-3" onchange="this.form.submit()">
                        <option value="">Any</option>
                        <option value="in_stock" @selected(request('stock_status')==='in_stock')>In Stock</option>
                        <option value="preorder" @selected(request('stock_status')==='preorder')>Pre-order</option>
                    </select>

                    <label class="form-label small text-uppercase text-muted">Pricing</label>
                    <select name="price_type" class="form-select mb-3" onchange="this.form.submit()">
                        <option value="">Any</option>
                        <option value="fixed" @selected(request('price_type')==='fixed')>Fixed Price</option>
                        <option value="sale" @selected(request('price_type')==='sale')>On Sale</option>
                        <option value="on_request" @selected(request('price_type')==='on_request')>Price on Request</option>
                    </select>

                    <a href="{{ url()->current() }}" class="btn btn-sm btn-outline-secondary w-100">Clear Filters</a>
                </div>
            </form>
        </div>

        <div class="col-lg-9">
            <div class="d-flex justify-content-end mb-3">
                <form method="GET" class="d-flex align-items-center gap-2">
                    <input type="hidden" name="q" value="{{ request('q') }}">
                    <input type="hidden" name="brand" value="{{ request('brand') }}">
                    <input type="hidden" name="category" value="{{ request('category') }}">
                    <label class="small text-muted mb-0">Sort:</label>
                    <select name="sort" class="form-select form-select-sm w-auto" onchange="this.form.submit()">
                        <option value="">Newest</option>
                        <option value="price_asc" @selected(request('sort')==='price_asc')>Price: Low to High</option>
                        <option value="price_desc" @selected(request('sort')==='price_desc')>Price: High to Low</option>
                        <option value="name" @selected(request('sort')==='name')>Name</option>
                    </select>
                </form>
            </div>

            @if($products && $products->count())
                <div class="row">
                    @foreach($products as $product)
                        @include('frontend.partials.product-card', ['product' => $product])
                    @endforeach
                </div>
                <div class="mt-3">{{ $products->links() }}</div>
            @else
                <div class="text-center text-muted py-5">
                    <i class="bi bi-search fs-1"></i>
                    <p class="mt-3">No products found. Try adjusting your filters{{ request('q') ? ' or search term' : '' }}.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
