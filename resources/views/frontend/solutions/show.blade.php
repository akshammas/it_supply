@extends('frontend.layouts.app')
@section('title', $solution->meta_title ?? $solution->name.' | '.config('app.name'))
@section('meta_description', $solution->meta_description ?? Str::limit(strip_tags($solution->short_description), 150))

@section('content')
@if($solution->image)
    <div style="height:280px; background:#111 url('{{ Storage::url($solution->image) }}') center/cover;" class="d-flex align-items-end">
        <div class="container pb-4">
            <h1 class="text-white">{{ $solution->name }}</h1>
        </div>
    </div>
@endif

<div class="container py-4">
    @if(!$solution->image)
        <h1 class="mb-3">{{ $solution->name }}</h1>
    @endif

    @if($solution->description)
        <div class="mb-4">{!! nl2br(e($solution->description)) !!}</div>
    @endif

    @if($products->isNotEmpty())
        <h4 class="mt-4 mb-3">Related Products</h4>
        <div class="row">
            @foreach($products as $product)
                @include('frontend.partials.product-card', ['product' => $product])
            @endforeach
        </div>
    @endif

    <div class="mt-4">
        <a href="https://wa.me/{{ \App\Models\Setting::get('whatsapp_number', '971500000000') }}?text={{ urlencode('Hello, I am interested in your '.$solution->name.' solution. Please provide more details.') }}"
           target="_blank" class="btn btn-success">
            <i class="bi bi-whatsapp me-1"></i> Ask About This Solution
        </a>
    </div>
</div>
@endsection
