<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', \App\Models\Setting::get('seo_title', config('app.name')))</title>
<meta name="description" content="@yield('meta_description', \App\Models\Setting::get('seo_description', 'Enterprise IT products and solutions for businesses across the UAE.'))">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .whatsapp-float {
            position: fixed; bottom: 20px; right: 20px; z-index: 1050;
            background: #25D366; color: #fff; border-radius: 50px;
            padding: 12px 20px; box-shadow: 0 4px 12px rgba(0,0,0,.2);
            text-decoration: none; font-weight: 600;
        }
        .whatsapp-float:hover { color: #fff; opacity: .9; }
        @media (max-width: 767px) {
            .whatsapp-float { left: 12px; right: 12px; text-align: center; bottom: 12px; border-radius: 8px; }
        }
    </style>
</head>
<body>

<header class="border-bottom bg-white sticky-top">
    <nav class="navbar navbar-expand-lg container py-3">
        <a class="navbar-brand fw-bold fs-4" href="{{ route('home') }}">{{ config('app.name') }}</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-lg-4">
                <li class="nav-item"><a class="nav-link" href="{{ route('products.index') }}">Products</a></li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">Brands</a>
                    <ul class="dropdown-menu">
                        @foreach(\App\Models\Brand::where('status', true)->orderBy('name')->take(12)->get() as $navBrand)
                            <li><a class="dropdown-item" href="{{ route('brands.show', $navBrand) }}">{{ $navBrand->name }}</a></li>
                        @endforeach
                    </ul>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">Categories</a>
                    <ul class="dropdown-menu">
                        @foreach(\App\Models\Category::whereNull('parent_id')->where('status', true)->orderBy('sort_order')->get() as $navCategory)
                            <li><a class="dropdown-item" href="{{ route('categories.show', $navCategory) }}">{{ $navCategory->name }}</a></li>
                        @endforeach
                    </ul>
                </li>
                <li class="nav-item"><a class="nav-link" href="#">Solutions</a></li>
                <li class="nav-item"><a class="nav-link" href="#">About</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('contact.create') }}">Contact</a></li>
            </ul>
            <a href="{{ route('enquiry.index') }}" class="btn btn-outline-secondary position-relative me-3">
                <i class="bi bi-cart3"></i>
                @php($cartCount = app(\App\Services\EnquiryCartService::class)->count())
                @if($cartCount > 0)
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">{{ $cartCount }}</span>
                @endif
            </a>
            <form action="{{ route('search') }}" method="GET" class="d-flex" role="search">
                <input type="search" name="q" value="{{ request('q') }}" class="form-control" placeholder="Search products...">
                <button class="btn btn-outline-primary ms-2"><i class="bi bi-search"></i></button>
            </form>
        </div>
    </nav>
</header>

<main>
    @yield('content')
</main>

<footer class="bg-dark text-white-50 py-5 mt-5">
    <div class="container">
        <div class="row">
            <div class="col-md-4 mb-3">
                <h5 class="text-white">{{ \App\Models\Setting::get('company_name', config('app.name')) }}</h5>
                <p class="small">Enterprise IT products and solutions for businesses across the UAE.</p>
            </div>
            <div class="col-md-4 mb-3">
                <h6 class="text-white">Quick Links</h6>
                <ul class="list-unstyled small">
                    <li><a href="{{ route('products.index') }}" class="text-white-50 text-decoration-none">Products</a></li>
                    <li><a href="#" class="text-white-50 text-decoration-none">About Us</a></li>
                    <li><a href="{{ route('contact.create') }}" class="text-white-50 text-decoration-none">Contact</a></li>
                </ul>
            </div>
            <div class="col-md-4 mb-3">
                <h6 class="text-white">Get in Touch</h6>
                <p class="small mb-0">{{ \App\Models\Setting::get('address', 'UAE | Dubai') }}</p>
            </div>
        </div>
        <hr class="border-secondary">
        <p class="small mb-0 text-center">&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
    </div>
</footer>

<a href="https://wa.me/{{ \App\Models\Setting::get('whatsapp_number', '971500000000') }}" target="_blank" class="whatsapp-float">
    <i class="bi bi-whatsapp me-1"></i> WhatsApp Us
</a>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/js/bootstrap.bundle.min.js"></script>
@yield('scripts')
</body>
</html>
