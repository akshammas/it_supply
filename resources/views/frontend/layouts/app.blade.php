<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', \App\Models\Setting::get('seo_title', config('app.name')))</title>
    <meta name="description" content="@yield('meta_description', \App\Models\Setting::get('seo_description', 'Enterprise IT products and solutions for businesses across the UAE.'))">
    <link rel="canonical" href="{{ url()->current() }}">

    <meta property="og:type" content="website">
    <meta property="og:title" content="@yield('title', \App\Models\Setting::get('seo_title', config('app.name')))">
    <meta property="og:description" content="@yield('meta_description', \App\Models\Setting::get('seo_description', ''))">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:image" content="@yield('og_image', \App\Models\Setting::get('logo') ? Storage::url(\App\Models\Setting::get('logo')) : '')">
    <meta name="twitter:card" content="summary_large_image">

   
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        :root {
            --brand-red: #E4002B;
            --brand-red-dark: #B5001F;
            --brand-red-light: #FFF1F3;
            --ink: #1A1A1A;
            --muted: #6B7280;
        }
        body { font-family: 'Inter', sans-serif; color: var(--ink); }

        /* ---- Header ---- */
        .site-header { border-bottom: 1px solid #eee; }
        .brand-logo { font-weight: 800; font-size: 1.4rem; color: var(--ink); letter-spacing: -.02em; }
        .brand-logo span { color: var(--brand-red); }
        .main-nav .nav-link { font-weight: 600; color: var(--ink); padding: .5rem 1rem; }
        .main-nav .nav-link:hover, .main-nav .nav-link.show { color: var(--brand-red); }
        .btn-brand { background: var(--brand-red); border-color: var(--brand-red); color: #fff; font-weight: 600; }
        .btn-brand:hover { background: var(--brand-red-dark); border-color: var(--brand-red-dark); color: #fff; }
        .btn-outline-brand { border: 1.5px solid var(--brand-red); color: var(--brand-red); font-weight: 600; }
        .btn-outline-brand:hover { background: var(--brand-red); color: #fff; }
        .quote-badge { background: var(--brand-red); }

        /* ---- Mega menu ----
           IMPORTANT: this is positioned absolute + left:0/right:0, which
           stretches it to fill its nearest *positioned* ancestor.
           That ancestor must be the <nav class="navbar ..."> (Bootstrap's
           .navbar is position:relative by default), NOT the <ul> of nav
           links — a <ul> is only as wide as its own links, so if the ul
           were the positioned ancestor the whole mega-menu (and all its
           category columns) would be squeezed into that narrow width and
           overlap. Do not add position-relative back onto the <ul>. */
        .mega-menu {
            position: absolute; left: 0; right: 0; top: 100%;
            background: #fff; border-top: 3px solid var(--brand-red);
            box-shadow: 0 12px 24px rgba(0,0,0,.08);
            padding: 1.75rem 0; display: none; z-index: 1030;
        }
        .mega-menu.show { display: block; }

        /* Column title: icon + label, both fixed to a common baseline */
        .mega-col-title {
            font-weight: 700; font-size: .95rem; color: var(--ink);
            margin-bottom: .6rem; display: flex; align-items: center; gap: .5rem;
        }
        /* Fixed-width icon slot so text below can indent by the exact same amount */
        .mega-col-icon {
            width: 22px; height: 22px; flex-shrink: 0;
            display: inline-flex; align-items: center; justify-content: center;
        }
        .mega-col-icon img { width: 22px; height: 22px; object-fit: contain; }
        .mega-col-icon i { font-size: 1rem; line-height: 1; }

        /* Column wrapper: prevent flex/grid children from ignoring
           min-content and forcing overlap when text is long (e.g.
           "Western Digital", "Keyboards & Mice") */
        .mega-col { min-width: 0; }
        .mega-col-title span:last-child {
            overflow-wrap: break-word;
            min-width: 0;
        }

        /* Sub-links: indented to line up under the title text, not the icon */
        .mega-col a {
            display: block; font-size: .85rem; color: var(--muted);
            text-decoration: none;
            padding: .2rem 0 .2rem 30px; /* 22px icon + .5rem (8px) gap = 30px */
        }
        .mega-col a:hover { color: var(--brand-red); }

        /* ---- Category strip ---- */
        .category-chip { text-decoration: none; text-align: center; display: block; }
        .category-chip .chip-icon {
            width: 64px; height: 64px; border-radius: 50%; background: var(--brand-red-light);
            display: flex; align-items: center; justify-content: center; margin: 0 auto .5rem;
            border: 1px solid #ffe0e5; transition: .15s;
        }
        .category-chip:hover .chip-icon { background: var(--brand-red); }
        .category-chip:hover .chip-icon i, .category-chip:hover .chip-icon img { filter: brightness(0) invert(1); }
        .category-chip .chip-label { font-size: .8rem; font-weight: 600; color: var(--ink); }
        .scroll-row { display: flex; gap: 1.25rem; overflow-x: auto; padding-bottom: .5rem; scrollbar-width: thin; }
        .scroll-row::-webkit-scrollbar { height: 6px; }
        .scroll-row::-webkit-scrollbar-thumb { background: #ddd; border-radius: 3px; }
        .scroll-row > * { flex: 0 0 auto; }

        /* ---- Brand strip ---- */
        .brand-tile {
            width: 130px; height: 80px; border: 1px solid #eee; border-radius: 8px;
            display: flex; align-items: center; justify-content: center; padding: .75rem;
            transition: .15s;
        }
        .brand-tile:hover { border-color: var(--brand-red); box-shadow: 0 4px 12px rgba(228,0,43,.1); }
        .brand-tile img { max-height: 60px; max-width: 100%; object-fit: contain; }

        /* ---- Section headers ---- */
        .section-eyebrow { color: var(--brand-red); font-weight: 700; font-size: .8rem; text-transform: uppercase; letter-spacing: .06em; }
        .section-title { font-weight: 800; font-size: 1.6rem; }

        /* ---- CTA band ---- */
        .cta-band { background: linear-gradient(120deg, var(--brand-red), var(--brand-red-dark)); color: #fff; }

        /* ---- WhatsApp float ---- */
        .whatsapp-float {
            position: fixed; bottom: 20px; right: 20px; z-index: 1050;
            background: #25D366; color: #fff; border-radius: 50px;
            padding: 12px 20px; box-shadow: 0 4px 12px rgba(0,0,0,.2);
            text-decoration: none; font-weight: 600;
        }
        .whatsapp-float:hover { color: #fff; opacity: .9; }
        @media (max-width: 767px) {
            .whatsapp-float { left: 12px; right: 12px; text-align: center; bottom: 12px; border-radius: 8px; }
            .mega-menu { position: static; box-shadow: none; }
        }

        footer.site-footer { background: #16181A; color: #b8bcc2; }
        footer.site-footer h5, footer.site-footer h6 { color: #fff; }
        footer.site-footer a { color: #b8bcc2; text-decoration: none; }
        footer.site-footer a:hover { color: var(--brand-red); }

        /* ---- Global Bootstrap color overrides ----
           Every other frontend page (listing, product detail, category,
           brand, contact, enquiry, quote-request) extends this same
           layout but still uses plain Bootstrap classes like btn-primary.
           Overriding them here, once, makes the red theme apply
           site-wide instead of needing every individual page restyled. */
        .btn-primary, .badge.bg-primary, .bg-primary { background-color: var(--brand-red) !important; border-color: var(--brand-red) !important; }
        .btn-primary:hover, .btn-primary:focus { background-color: var(--brand-red-dark) !important; border-color: var(--brand-red-dark) !important; }
        .btn-outline-primary { color: var(--brand-red) !important; border-color: var(--brand-red) !important; }
        .btn-outline-primary:hover { background-color: var(--brand-red) !important; color: #fff !important; }
        .text-primary { color: var(--brand-red) !important; }
        .border-primary { border-color: var(--brand-red) !important; }
        main a:not(.btn):not(.dropdown-item):not(.nav-link) { color: var(--brand-red); }
        .page-link { color: var(--brand-red); }
        .page-item.active .page-link { background-color: var(--brand-red); border-color: var(--brand-red); }
        .form-control:focus, .form-select:focus { border-color: var(--brand-red); box-shadow: 0 0 0 .25rem rgba(228,0,43,.15); }
        /* WhatsApp buttons deliberately stay green (btn-success) — the
           contrast against the red theme is intentional, same pattern
           the reference site uses. */
    </style>
</head>
<body>

<header class="site-header bg-white sticky-top">
    <nav class="navbar navbar-expand-lg container py-3">
        <a class="brand-logo text-decoration-none" href="{{ route('home') }}">
            {{ \App\Models\Setting::get('company_name', config('app.name')) }}<span>.</span>
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
                    <a class="nav-link d-flex align-items-center gap-1" href="{{ route('products.index') }}" id="productsMegaToggle">
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
                                                @if($cat->image)
                                                    <img src="{{ Storage::url($cat->image) }}" alt="">
                                                @else
                                                    <i class="bi bi-box2 text-danger"></i>
                                                @endif
                                            </span>
                                            <span>{{ $cat->name }}</span>
                                        </div>
                                        <a href="{{ route('categories.show', $cat) }}" class="fw-semibold" style="color:var(--ink)">View All</a>
                                        @foreach($cat->menuBrands as $mb)
                                            <a href="{{ route('brands.show', $mb) }}">{{ $mb->name }}</a>
                                        @endforeach
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </li>
                <li class="nav-item"><a class="nav-link" href="{{ route('about') }}">About us</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('blog.index') }}">Blog</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('contact.create') }}">Contact Us</a></li>
            </ul>

            <form action="{{ route('search') }}" method="GET" class="d-flex me-3" role="search">
                <input type="search" name="q" value="{{ request('q') }}" class="form-control" placeholder="Search products...">
                <button class="btn btn-outline-brand ms-2"><i class="bi bi-search"></i></button>
            </form>

            <a href="{{ route('enquiry.index') }}" class="btn btn-outline-brand position-relative">
                <i class="bi bi-cart3 me-1"></i> Quote List
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

<a href="https://wa.me/{{ \App\Models\Setting::get('whatsapp_number', '971500000000') }}" target="_blank" class="whatsapp-float">
    <i class="bi bi-whatsapp me-1"></i> WhatsApp Us
</a>


<script>
    // Simple hover-controlled mega menu (desktop) / click-to-toggle (mobile)
    const megaWrap = document.getElementById('productsMegaWrap');
    const megaMenu = document.getElementById('productsMega');
    const megaToggle = document.getElementById('productsMegaToggle');
    let hideTimer;

    function showMega() { clearTimeout(hideTimer); megaMenu.classList.add('show'); }
    function hideMegaDelayed() { hideTimer = setTimeout(() => megaMenu.classList.remove('show'), 150); }

    if (window.innerWidth > 991) {
        megaWrap.addEventListener('mouseenter', showMega);
        megaWrap.addEventListener('mouseleave', hideMegaDelayed);
    } else {
        megaToggle.addEventListener('click', function (e) {
            e.preventDefault();
            megaMenu.classList.toggle('show');
        });
    }
</script>
@yield('scripts')
</body>
</html>