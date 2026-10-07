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

/* ---- Nav links: sliding underline ---- */
.main-nav .nav-link { position: relative; }
.main-nav .nav-link::after {
    content: ""; position: absolute; left: 1rem; right: 1rem; bottom: 2px; height: 2px;
    background: var(--brand-red); border-radius: 2px;
    transform: scaleX(0); transform-origin: left; transition: transform .25s ease;
}
.main-nav .nav-link:hover::after, .main-nav .nav-link.show::after { transform: scaleX(1); }
#productsMegaToggle .bi-chevron-down { transition: transform .25s ease; }
#productsMegaToggle.show .bi-chevron-down { transform: rotate(180deg); }

/* ---- Mega menu ----
   Positioned against the <nav class="navbar"> (not the <ul>), so
   keep position:relative off the <ul>. */
.mega-menu {
    position: absolute; left: 0; right: 0; top: 100%;
    background: #fff; border-top: 3px solid var(--brand-red);
    border-radius: 0 0 16px 16px;
    box-shadow: 0 24px 48px -12px rgba(16,24,40,.18);
    padding: 1.25rem 0 1.5rem; z-index: 1030;
    opacity: 0; visibility: hidden; transform: translateY(12px); pointer-events: none;
    transition: opacity .22s ease, transform .22s ease, visibility 0s linear .22s;
}
.mega-menu.show {
    opacity: 1; visibility: visible; transform: none; pointer-events: auto;
    transition-delay: 0s;
}

/* Each column = a soft card that wakes up on hover */
.mega-col {
    position: relative; isolation: isolate; min-width: 0;
    padding: .9rem calc(var(--bs-gutter-x) * .5 + .9rem);
}
.mega-col::before {            /* card background */
    content: ""; position: absolute; z-index: -1;
    inset: 0 calc(var(--bs-gutter-x) * .5);
    border-radius: 12px; border: 1px solid transparent; background: transparent;
    transition: background .2s ease, border-color .2s ease, box-shadow .2s ease;
}
.mega-col::after {             /* red bar that grows across the top */
    content: ""; position: absolute; top: 0;
    left: calc(var(--bs-gutter-x) * .5 + 14px); right: calc(var(--bs-gutter-x) * .5 + 14px);
    height: 3px; border-radius: 0 0 3px 3px; background: var(--brand-red);
    transform: scaleX(0); transition: transform .3s ease;
}
.mega-col:hover::before { background: #fafbfc; border-color: #eceef1; box-shadow: 0 10px 24px -12px rgba(16,24,40,.18); }
.mega-col:hover::after  { transform: scaleX(1); }

/* Column title: icon bubble + label */
.mega-col-title {
    font-weight: 700; font-size: .95rem; color: var(--ink);
    margin-bottom: .5rem; display: flex; align-items: center; gap: .5rem;
    transition: color .2s ease;
}
.mega-col-title span:last-child { overflow-wrap: break-word; min-width: 0; }
.mega-col-icon {
    width: 32px; height: 32px; flex-shrink: 0; border-radius: 10px;
    background: var(--brand-red-light);
    display: inline-flex; align-items: center; justify-content: center;
    transition: background .2s ease, transform .25s ease;
}
.mega-col-icon img { width: 20px; height: 20px; object-fit: contain; transition: filter .2s ease; }
.mega-col-icon i { font-size: 1rem; line-height: 1; transition: color .2s ease; }
.mega-col:hover .mega-col-title { color: var(--brand-red); }
.mega-col:hover .mega-col-icon  { background: var(--brand-red); transform: rotate(-6deg) scale(1.08); }
.mega-col:hover .mega-col-icon img { filter: brightness(0) invert(1); }
.mega-col:hover .mega-col-icon i   { color: #fff !important; }

/* Links: indented under the title text (32px icon + 8px gap = 40px) */
.mega-col a {
    position: relative; display: block; font-size: .86rem; color: var(--ink);
    text-decoration: none; padding: .3rem .5rem .3rem 40px; border-radius: 6px;
    transition: color .15s ease, background .15s ease, padding-left .2s ease;
}
.mega-col a:hover::before { width: 10px; }
.mega-col a:hover { color: var(--brand-red); background: var(--brand-red-light); padding-left: 46px; }
.mega-col a:hover::before { width: 10px; }

.mega-col a.mega-all { color: var(--brand-red); font-weight: 600; margin-bottom: .15rem; }
.mega-col a.mega-all::after {
    content: "\2192"; margin-left: .4rem; display: inline-block; transition: transform .2s ease;
}
.mega-col a.mega-all:hover::after { transform: translateX(5px); }

/* Columns fade up one after another when the menu opens */
@keyframes megaColIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: none; } }
.mega-menu.show .mega-col { animation: megaColIn .35s ease both; }
.mega-menu.show .mega-col:nth-child(2) { animation-delay: .04s; }
.mega-menu.show .mega-col:nth-child(3) { animation-delay: .08s; }
.mega-menu.show .mega-col:nth-child(4) { animation-delay: .12s; }
.mega-menu.show .mega-col:nth-child(5) { animation-delay: .16s; }
.mega-menu.show .mega-col:nth-child(6) { animation-delay: .20s; }
.mega-menu.show .mega-col:nth-child(7) { animation-delay: .24s; }
.mega-menu.show .mega-col:nth-child(8) { animation-delay: .28s; }

@media (prefers-reduced-motion: reduce) {
    .mega-menu, .mega-col, .mega-col *, .main-nav .nav-link::after { transition: none !important; animation: none !important; }
}

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
            .mega-menu {
                position: static; box-shadow: none; border-radius: 0; padding: .75rem 0;
                display: none; opacity: 1; visibility: visible; transform: none; pointer-events: auto; transition: none;
            }
            .mega-menu.show { display: block; }
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

        @media (min-width: 992px) {
        .promo-single .promo-img {
            max-height: 200px;   /* change this number to taste */
                object-fit: cover;
            }
        }
        /* ===== Hero carousel animation ===== */
#heroCarousel .hero-bg {
    position: absolute;
    inset: 0;
    background-size: cover;
    background-position: center;
    will-change: transform;
}

/* Slower cross-fade (Bootstrap's default is 0.6s) */
#heroCarousel.carousel-fade .carousel-item {
    transition-duration: 1s;
}
#heroCarousel.carousel-fade .active.carousel-item-start,
#heroCarousel.carousel-fade .active.carousel-item-end {
    transition: opacity 0s 1s;   /* keep this delay equal to the duration above */
}

/* Slow zoom + text entrance, starting as the next slide begins to fade in */
#heroCarousel .carousel-item:is(.active, .carousel-item-next, .carousel-item-prev) .hero-bg {
    animation: heroZoom 9s ease-out both;
}
#heroCarousel .carousel-item:is(.active, .carousel-item-next, .carousel-item-prev) .hero-anim {
    animation: heroFadeUp .9s cubic-bezier(.22, .61, .36, 1) both;
}
#heroCarousel .carousel-item:is(.active, .carousel-item-next, .carousel-item-prev) .hero-anim:nth-child(1) { animation-delay: .25s; }
#heroCarousel .carousel-item:is(.active, .carousel-item-next, .carousel-item-prev) .hero-anim:nth-child(2) { animation-delay: .45s; }
#heroCarousel .carousel-item:is(.active, .carousel-item-next, .carousel-item-prev) .hero-anim:nth-child(3) { animation-delay: .65s; }

@keyframes heroZoom {
    from { transform: scale(1); }
    to   { transform: scale(1.08); }
}
@keyframes heroFadeUp {
    from { opacity: 0; transform: translateY(24px); }
    to   { opacity: 1; transform: translateY(0); }
}

/* Smooth button hover */
#heroCarousel .btn {
    transition: transform .2s ease, box-shadow .2s ease, background-color .2s ease, color .2s ease;
}
#heroCarousel .btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 18px rgba(0, 0, 0, .25);
}

/* Respect visitors who turn animations off in their system settings */
@media (prefers-reduced-motion: reduce) {
    #heroCarousel .hero-bg,
    #heroCarousel .hero-anim { animation: none !important; }
}
@media (max-width: 767px) {
    .mega-menu { position: static; box-shadow: none; }
}
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

@include('frontend.partials.whatsapp-chat')


<script>
    // Simple hover-controlled mega menu (desktop) / click-to-toggle (mobile)
    const megaWrap = document.getElementById('productsMegaWrap');
    const megaMenu = document.getElementById('productsMega');
    const megaToggle = document.getElementById('productsMegaToggle');
    let hideTimer;

   function showMega() {
        clearTimeout(hideTimer);
        megaMenu.classList.add('show');
        megaToggle.classList.add('show');
    }
    function hideMegaDelayed() {
        hideTimer = setTimeout(() => {
            megaMenu.classList.remove('show');
            megaToggle.classList.remove('show');
        }, 150);
    }

    if (window.innerWidth > 991) {
        megaWrap.addEventListener('mouseenter', showMega);
        megaWrap.addEventListener('mouseleave', hideMegaDelayed);
        megaWrap.addEventListener('focusin', showMega);      // keyboard: Tab into the menu
        megaWrap.addEventListener('focusout', hideMegaDelayed);
        document.addEventListener('keydown', e => { if (e.key === 'Escape') hideMegaDelayed(); });
    } else {
        megaToggle.addEventListener('click', function (e) {
            e.preventDefault();
            megaMenu.classList.toggle('show');
            megaToggle.classList.toggle('show');
        });
    }
</script>
@yield('scripts')
</body>
</html>