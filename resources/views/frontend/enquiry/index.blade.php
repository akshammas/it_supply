@extends('frontend.layouts.app')
@section('title', 'Your Enquiry List | '.config('app.name'))

@section('content')
<div class="container py-4">
    <h2 class="mb-4">Your Enquiry List</h2>

    @if(session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif

    @if($items->isEmpty())
        <div class="text-center text-muted py-5">
            <i class="bi bi-cart fs-1"></i>
            <p class="mt-3">Your enquiry list is empty.</p>
            <a href="{{ route('products.index') }}" class="btn btn-primary">Browse Products</a>
        </div>
    @else
        <div class="card shadow-sm mb-4">
            <table class="table align-middle mb-0">
                <thead class="table-light">
                    <tr><th>Product</th><th style="width:140px">Quantity</th><th class="text-end">Actions</th></tr>
                </thead>
                <tbody>
                    @foreach($items as $item)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    @if($item['product']->primaryImage)
                                        <img src="{{ Storage::url($item['product']->primaryImage->image) }}" style="width:48px; height:48px; object-fit:contain;">
                                    @endif
                                    <div>
                                        <a href="{{ route('products.show', $item['product']) }}" class="text-decoration-none">{{ $item['product']->name }}</a>
                                        <div class="text-muted small">SKU: {{ $item['product']->sku }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <form method="POST" action="{{ route('enquiry.update', $item['product']) }}" class="d-flex align-items-center gap-2">
                                    @csrf
                                    <input type="number" name="quantity" value="{{ $item['quantity'] }}" min="1" class="form-control form-control-sm" style="width:70px">
                                    <button class="btn btn-sm btn-outline-secondary">Update</button>
                                </form>
                            </td>
                            <td class="text-end">
                                <form method="POST" action="{{ route('enquiry.remove', $item['product']) }}" onsubmit="return confirm('Remove this item?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger">Remove</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('quote-request.create') }}" class="btn btn-primary btn-lg">
                <i class="bi bi-file-earmark-text me-1"></i> Request Quote
            </a>
            <a href="https://wa.me/{{ config('services.whatsapp.number', '971500000000') }}?text={{ urlencode($whatsappMessage) }}"
               target="_blank" class="btn btn-success btn-lg">
                <i class="bi bi-whatsapp me-1"></i> WhatsApp
            </a>
            <a href="{{ route('products.index') }}" class="btn btn-outline-secondary btn-lg">Continue Browsing</a>
        </div>
    @endif
</div>
@endsection
