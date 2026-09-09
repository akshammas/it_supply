@extends('frontend.layouts.app')
@section('title', 'Thank You | '.config('app.name'))

@section('content')
<div class="container py-5 text-center">
    <i class="bi bi-check-circle-fill text-success" style="font-size:4rem;"></i>
    <h2 class="mt-3">Thank you for your enquiry</h2>
    <p class="text-muted">Our sales team will get back to you shortly with pricing and availability.</p>
    <a href="{{ route('products.index') }}" class="btn btn-primary mt-3">Continue Browsing</a>
</div>
@endsection
