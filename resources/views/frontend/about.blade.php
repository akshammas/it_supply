@extends('frontend.layouts.app')

@section('title', 'About Us | ' . \App\Models\Setting::get('company_name', config('app.name')))
@section('meta_description', 'Learn about ' . \App\Models\Setting::get('company_name', config('app.name')) . ', a trusted supplier of enterprise IT products and solutions across the UAE.')

@section('content')

{{-- Hero --}}
<section class="py-5" style="background: var(--brand-red-light);">
    <div class="container py-4">
        <div class="row align-items-center g-4">
            <div class="col-lg-7">
                <div class="section-eyebrow mb-2">About Us</div>
                <h1 class="fw-bold display-5 mb-3">
                    Your trusted partner for enterprise IT supply in the UAE
                </h1>
                <p class="lead text-muted mb-4">
                    {{ \App\Models\Setting::get('company_name', config('app.name')) }} supplies genuine IT hardware
                    and networking products from leading global brands to businesses across the Emirates.
                </p>
                <a href="{{ route('products.index') }}" class="btn btn-brand btn-lg me-2">Browse Products</a>
                <a href="{{ route('contact.create') }}" class="btn btn-outline-brand btn-lg">Contact Us</a>
            </div>
            <div class="col-lg-5 text-center d-none d-lg-block">
                <i class="bi bi-hdd-network" style="font-size: 9rem; color: var(--brand-red); opacity: .85;"></i>
            </div>
        </div>
    </div>
</section>

{{-- Who we are --}}
<section class="py-5">
    <div class="container">
        <div class="row g-5 align-items-center">
            <div class="col-lg-6">
                <div class="section-eyebrow mb-2">Who We Are</div>
                <h2 class="section-title mb-3">Built around reliable supply and honest service</h2>
                <p class="text-muted">
                    We help companies, government bodies and IT resellers source the equipment they need:
                    servers, storage, networking, laptops, peripherals and more, at competitive prices and
                    with dependable delivery.
                </p>
                <p class="text-muted mb-0">
                    Our team works directly with manufacturers and authorised distributors, so you get genuine
                    products with proper warranty, and a single point of contact from quotation to delivery.
                </p>
            </div>
            <div class="col-lg-6">
                <div class="row g-3 text-center">
                    {{-- Replace these figures with your real numbers --}}
                    <div class="col-6">
                        <div class="border rounded-3 p-4 h-100">
                            <div class="fw-bold fs-1" style="color: var(--brand-red);">10+</div>
                            <div class="small text-muted">Years in the market</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="border rounded-3 p-4 h-100">
                            <div class="fw-bold fs-1" style="color: var(--brand-red);">50+</div>
                            <div class="small text-muted">Global brands</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="border rounded-3 p-4 h-100">
                            <div class="fw-bold fs-1" style="color: var(--brand-red);">1000+</div>
                            <div class="small text-muted">Products available</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="border rounded-3 p-4 h-100">
                            <div class="fw-bold fs-1" style="color: var(--brand-red);">500+</div>
                            <div class="small text-muted">Business customers</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- What we do --}}
<section class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <div class="section-eyebrow mb-2">What We Do</div>
            <h2 class="section-title">Everything your business needs, in one place</h2>
        </div>
        <div class="row g-4">
            @php
                $services = [
                    ['bi-pc-display',   'IT Hardware',        'Desktops, laptops, servers, storage and workstations from trusted manufacturers.'],
                    ['bi-router',       'Networking',         'Switches, routers, wireless and security equipment for offices of every size.'],
                    ['bi-keyboard',     'Peripherals',        'Monitors, keyboards, mice, printers and accessories to complete your setup.'],
                    ['bi-receipt',      'Quotations & Bulk',  'Fast, competitive quotes for single items or large project orders.'],
                ];
            @endphp
            @foreach($services as [$icon, $title, $text])
                <div class="col-md-6 col-lg-3">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body p-4">
                            <div class="rounded-circle d-flex align-items-center justify-content-center mb-3"
                                 style="width:56px;height:56px;background:var(--brand-red-light);">
                                <i class="bi {{ $icon }}" style="font-size:1.5rem;color:var(--brand-red);"></i>
                            </div>
                            <h5 class="fw-bold">{{ $title }}</h5>
                            <p class="small text-muted mb-0">{{ $text }}</p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- Why choose us --}}
<section class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <div class="section-eyebrow mb-2">Why Choose Us</div>
            <h2 class="section-title">What sets us apart</h2>
        </div>
        <div class="row g-4">
            @php
                $reasons = [
                    ['bi-patch-check',  'Genuine Products',   'Sourced from authorised channels with manufacturer warranty.'],
                    ['bi-tag',          'Competitive Pricing','Direct supplier relationships keep your costs down.'],
                    ['bi-truck',        'Reliable Delivery',  'Prompt dispatch and delivery across the UAE.'],
                    ['bi-headset',      'Expert Support',     'Knowledgeable staff who help you pick the right product.'],
                ];
            @endphp
            @foreach($reasons as [$icon, $title, $text])
                <div class="col-sm-6 col-lg-3 text-center">
                    <i class="bi {{ $icon }} mb-3 d-inline-block" style="font-size:2.5rem;color:var(--brand-red);"></i>
                    <h6 class="fw-bold">{{ $title }}</h6>
                    <p class="small text-muted mb-0">{{ $text }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- Contact details --}}
<section class="py-5 bg-light">
    <div class="container">
        <div class="row g-4 text-center">
            <div class="col-md-4">
                <i class="bi bi-geo-alt fs-2" style="color:var(--brand-red);"></i>
                <h6 class="fw-bold mt-2">Address</h6>
                <p class="small text-muted mb-0">{{ \App\Models\Setting::get('address', 'UAE | Dubai') }}</p>
            </div>
            <div class="col-md-4">
                <i class="bi bi-envelope fs-2" style="color:var(--brand-red);"></i>
                <h6 class="fw-bold mt-2">Email</h6>
                <p class="small text-muted mb-0">{{ \App\Models\Setting::get('email', 'info@example.com') }}</p>
            </div>
            <div class="col-md-4">
                <i class="bi bi-telephone fs-2" style="color:var(--brand-red);"></i>
                <h6 class="fw-bold mt-2">Phone</h6>
                <p class="small text-muted mb-0">{{ \App\Models\Setting::get('phone', '+971 00 000 0000') }}</p>
            </div>
        </div>
    </div>
</section>

{{-- CTA --}}
<section class="cta-band py-5">
    <div class="container text-center py-3">
        <h2 class="fw-bold mb-2">Need a quotation?</h2>
        <p class="mb-4 opacity-75">Add products to your quote list and our team will get back to you quickly.</p>
        <a href="{{ route('products.index') }}" class="btn btn-light btn-lg fw-semibold me-2" style="color:var(--brand-red);">
            Browse Products
        </a>
        <a href="{{ route('contact.create') }}" class="btn btn-outline-light btn-lg fw-semibold">Contact Us</a>
    </div>
</section>

@endsection