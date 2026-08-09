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
