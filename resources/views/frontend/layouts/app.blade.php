<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', \App\Models\Setting::get('seo_title', config('app.name')))</title>
    <meta name="description" content="@yield('meta_description', \App\Models\Setting::get('seo_description', 'Enterprise IT products and solutions for businesses across the UAE.'))">
    <link rel="canonical" href="{{ url()->current() }}">
    @include('frontend.partials.favicon')

    <meta property="og:type" content="website">
    <meta property="og:title" content="@yield('title', \App\Models\Setting::get('seo_title', config('app.name')))">
    <meta property="og:description" content="@yield('meta_description', \App\Models\Setting::get('seo_description', ''))">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:image" content="@yield('og_image', \App\Models\Setting::get('logo') ? Storage::url(\App\Models\Setting::get('logo')) : '')">
    <meta name="twitter:card" content="summary_large_image">

   
    @vite(['resources/css/app.css', 'resources/css/frontend.css', 'resources/js/app.js'])


</head>
<body>

<header class="site-header bg-white sticky-top">
    <nav class="navbar navbar-expand-lg container py-3">
        @php
            $siteName = \App\Models\Setting::get('company_name', config('app.name'));
            $siteLogo = \App\Models\Setting::get('logo');
        @endphp
        <a class="brand-logo text-decoration-none d-flex align-items-center" href="{{ route('home') }}">
            @if($siteLogo)
                <img src="{{ Storage::url($siteLogo) }}" alt="{{ $siteName }}" class="brand-logo-img">
            @else
                {{ $siteName }}<span>.</span>
            @endif
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="mainNav">
            <!-- NOTE: position-relative removed from this <ul> on purpose.
                 Bootstrap's .navbar (the parent <nav>) is already
                 position:relative by default, so the mega-menu's
                 absolute left:0/right:0 now spans the full nav container
                 width instead of just this list's width. Do not re-add
                 position-relative here — it will reintroduce the
                 overlapping/squeezed-columns bug. -->
            <ul class="navbar-nav main-nav me-auto mb-2 mb-lg-0 ms-lg-4">
                <li class="nav-item position-static" id="productsMegaWrap">
                    <a class="nav-link d-flex align-items-center gap-1 {{ request()->routeIs('products.*', 'categories.*', 'brands.*') ? 'active' : '' }}"
                        href="{{ route('products.index') }}" id="productsMegaToggle">
                            Products <i class="bi bi-chevron-down small"></i>
                    </a>
                    <div class="mega-menu" id="productsMega">
                        <div class="container">
                            <div class="row g-4">
                                @php
                                    $megaCategories = \Illuminate\Support\Facades\Cache::remember('nav.mega', now()->addHour(), function () {
                                        $cats = \App\Models\Category::whereNull('parent_id')->where('status', true)->orderBy('sort_order')->take(8)->get();
                                        foreach ($cats as $cat) {
                                            $cat->menuBrands = \App\Models\Brand::whereHas('products', fn($q) => $q->where('category_id', $cat->id)->where('status', true))
                                                ->where('status', true)->orderBy('name')->take(6)->get();
                                        }
                                        return $cats;
                                    });
                                @endphp
                                @foreach($megaCategories as $cat)
                                    <div class="col-6 col-md-3 mega-col">
                                        <div class="mega-col-title">
                                            <span class="mega-col-icon">
                                                @include('frontend.partials.category-icon', ['category' => $cat, 'size' => 22, 'iconClass' => 'text-danger'])
                                            </span>
                                            <span>{{ $cat->name }}</span>
                                        </div>
                                        <a href="{{ route('categories.show', $cat) }}" class="mega-all">View All</a>
                                        @foreach($cat->menuBrands as $mb)
                                            <a href="{{ route('products.index', ['category' => $cat->slug, 'brand' => $mb->slug]) }}">{{ $mb->name }}</a>
                                        @endforeach
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('about') ? 'active' : '' }}" href="{{ route('about') }}">About Us</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('blog.*') ? 'active' : '' }}" href="{{ route('blog.index') }}">Blog</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('contact.*') ? 'active' : '' }}" href="{{ route('contact.create') }}">Contact Us</a>
                </li>
            </ul>

            <form action="{{ route('search') }}" method="GET" class="d-flex me-3" role="search">
                <input type="search" name="q" value="{{ request('q') }}" class="form-control" placeholder="Search products...">
                <button class="btn btn-outline-brand ms-2"><i class="bi bi-search"></i></button>
            </form>

            <a href="{{ route('enquiry.index') }}" class="btn btn-outline-brand position-relative">
                <i class="bi bi-cart3 me-1"></i>
                @php($cartCount = app(\App\Services\EnquiryCartService::class)->count())
                @if($cartCount > 0)
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill quote-badge">{{ $cartCount }}</span>
                @endif
            </a>
        </div>
    </nav>
</header>

<main>
    @yield('content')
</main>

<footer class="site-footer py-5 mt-5">
    <div class="container">
        <div class="row g-4">
            <div class="col-md-4">
                <h5>{{ \App\Models\Setting::get('company_name', config('app.name')) }}</h5>
                <p class="small">Enterprise IT products and solutions for businesses across the UAE.</p>
            </div>
            <div class="col-md-4">
                <h6>Quick Links</h6>
                <ul class="list-unstyled small">
                    <li class="mb-1"><a href="{{ route('products.index') }}">Products</a></li>
                    <li class="mb-1"><a href="{{ route('about') }}">about us</a></li>
                    <li class="mb-1"><a href="{{ route('blog.index') }}">Blog</a></li>
                    <li class="mb-1"><a href="{{ route('contact.create') }}">Contact</a></li>
                    @foreach(\App\Models\Page::where('status', true)->orderBy('title')->get() as $footerPage)
                        <li class="mb-1"><a href="{{ route('pages.show', $footerPage) }}">{{ $footerPage->title }}</a></li>
                    @endforeach
                </ul>
            </div>
            <div class="col-md-4">
                <h6>Get in Touch</h6>
                <p class="small mb-1">{{ \App\Models\Setting::get('address', 'UAE | Dubai') }}</p>
                @if(\App\Models\Setting::get('email'))<p class="small mb-1">{{ \App\Models\Setting::get('email') }}</p>@endif
                @if(\App\Models\Setting::get('phone'))<p class="small mb-1">{{ \App\Models\Setting::get('phone') }}</p>@endif
            </div>
        </div>
        <hr style="border-color:#2a2d30;">
        <p class="small mb-0 text-center">&copy; {{ date('Y') }} {{ \App\Models\Setting::get('company_name', config('app.name')) }}. All rights reserved.</p>
    </div>
</footer>

@include('frontend.partials.whatsapp-chat')


<script>
  const megaWrap = document.getElementById('productsMegaWrap');
const megaMenu = document.getElementById('productsMega');
const megaToggle = document.getElementById('productsMegaToggle');
const isDesktop = () => window.matchMedia('(min-width: 992px)').matches;
let hideTimer;

function showMega() {
    clearTimeout(hideTimer);
    megaMenu.classList.add('show');
    megaToggle.classList.add('show');
}
function hideMega() {
    megaMenu.classList.remove('show');
    megaToggle.classList.remove('show');
}
function hideMegaDelayed() { hideTimer = setTimeout(hideMega, 150); }

// Desktop: hover and keyboard focus
megaWrap.addEventListener('mouseenter', () => { if (isDesktop()) showMega(); });
megaWrap.addEventListener('mouseleave', () => { if (isDesktop()) hideMegaDelayed(); });
megaWrap.addEventListener('focusin', () => { if (isDesktop()) showMega(); });
megaWrap.addEventListener('focusout', () => { if (isDesktop()) hideMegaDelayed(); });
document.addEventListener('keydown', e => { if (e.key === 'Escape') hideMega(); });

// Mobile and tablet: tap "Products" to open or close the list
megaToggle.addEventListener('click', function (e) {
    if (isDesktop()) return;           // on desktop the link goes to the products page
    e.preventDefault();
    megaMenu.classList.toggle('show');
    megaToggle.classList.toggle('show');
});

// Going back to desktop width resets the mobile open state
window.matchMedia('(min-width: 992px)').addEventListener('change', hideMega);
</script>
@yield('scripts')
</body>
</html>