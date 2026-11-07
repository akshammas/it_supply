@php
    $company = \App\Models\Setting::get('company_name', config('app.name'));
    $email   = \App\Models\Setting::get('email', 'info@example.com');
    $phone   = \App\Models\Setting::get('phone', '+971 00 000 0000');
    $tel     = preg_replace('/[^\d+]/', '', $phone);

    $pageTitle = config('app.name') . ' | Enterprise IT Products & Solutions in UAE';
    $pageDesc  = \Illuminate\Support\Str::limit(
        'Buy genuine enterprise IT products in the UAE: servers, storage, networking, laptops and peripherals. Request a quote from ' . $company . ' in Dubai.',
        158
    );
    $pageUrl = url('/');
    $aboutUrl = \Illuminate\Support\Facades\Route::has('about') ? route('about') : url('/about');

    // Optional photo: public/images/home/delivery.jpg (falls back to an SVG route illustration)
    $deliveryImg = file_exists(public_path('images/home/delivery.jpg')) ? asset('images/home/delivery.jpg') : null;

    // Figures are shared with the About page. Edit them in Settings.
    $facts = [];
    foreach ([
        [\App\Models\Setting::get('about_years', '10+'),      'Years in the market'],
        [\App\Models\Setting::get('about_brands', '50+'),     'Global brands'],
        [\App\Models\Setting::get('about_products', '1000+'), 'Products available'],
        [\App\Models\Setting::get('about_customers', '500+'), 'Business customers'],
    ] as [$raw, $label]) {
        $ok = preg_match('/^(\D*)([\d,]+)(.*)$/', (string) $raw, $m);
        $facts[] = ['pre' => $ok ? $m[1] : '', 'to' => $ok ? (int) str_replace(',', '', $m[2]) : null, 'suf' => $ok ? $m[3] : '', 'raw' => $raw, 'label' => $label];
    }

    // Sample lines for the quote-list illustration: real featured products when available.
    $mockNames = $featuredProducts->isNotEmpty()
        ? $featuredProducts->take(3)->pluck('name')->all()
        : ['Rack server', '24-port switch', 'Business laptop'];
    $mockQty = [10, 25, 5];

    $buyers = [
        ['bi-building', 'Companies',         'IT teams and office managers buying for staff, branches and new sites. One contact handles the order from quotation to delivery.', 'Get a quote for your company'],
        ['bi-bank',     'Government bodies', 'Written quotations for the equipment your department needs, with genuine products and manufacturer warranty.',                       'Get a quote for your department'],
        ['bi-shop',     'IT resellers',      'Stock for your own customers, with quotations for bulk orders.',                                                                        'Get a reseller quote'],
    ];

    $why = [
        ['bi-patch-check', 'Genuine products',      'Sourced from manufacturers and authorised distributors.'],
        ['bi-tags',        'Competitive pricing',   'Direct supplier relationships keep costs down.'],
        ['bi-truck',       'UAE delivery',          'Prompt dispatch to your office or site.'],
        ['bi-headset',     'Technical support',     'Staff who help you compare models and choose the right product.'],
        ['bi-shield-check','Warranty support',      'Products carry the manufacturer’s warranty.'],
        ['bi-receipt',     'Bulk and project quotes','One laptop or a full project list. We quote both.'],
    ];

    $faqs = [
        ['Are your IT products genuine?', 'Yes. We buy from manufacturers and authorised distributors, so products are genuine.'],
        ['Do products come with a warranty?', 'Products carry the manufacturer’s warranty. Contact us if you need warranty details for a specific model.'],
        ['Do you deliver outside Dubai?', 'We deliver across the UAE. Send us your location and we will confirm delivery details with your quotation.'],
        ['Can I order a single item, or only in bulk?', 'Both. We quote single items as well as large project orders.'],
        ['How do I get a quote?', 'Add products to your quote list from the catalogue, or contact us with your list and quantities. Our team replies with pricing and availability.'],
        ['What if I cannot find a product in the catalogue?', 'Send us the model or part number and we will check whether we can supply it.'],
    ];

    $graph = [
        '@context' => 'https://schema.org',
        '@graph' => [
            [
                '@type' => 'Organization',
                '@id'   => $pageUrl . '#organization',
                'name'  => $company,
                'url'   => $pageUrl,
                'email' => $email,
                'telephone' => $phone,
                'areaServed' => ['@type' => 'Country', 'name' => 'United Arab Emirates'],
            ],
            [
                '@type' => 'WebSite',
                '@id'   => $pageUrl . '#website',
                'url'   => $pageUrl,
                'name'  => $company,
                'publisher' => ['@id' => $pageUrl . '#organization'],
            ],
            [
                '@type' => 'FAQPage',
                'mainEntity' => array_map(fn ($f) => [
                    '@type' => 'Question',
                    'name'  => $f[0],
                    'acceptedAnswer' => ['@type' => 'Answer', 'text' => $f[1]],
                ], $faqs),
            ],
        ],
    ];
    $jsonFlags = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG;
@endphp
@extends('frontend.layouts.app')

@section('title', $pageTitle)
@section('meta_description', $pageDesc)

{{-- Needs @stack('head') before </head> in the layout. --}}
@push('head')
<link rel="canonical" href="{{ $pageUrl }}">
<meta property="og:type" content="website">
<meta property="og:site_name" content="{{ $company }}">
<meta property="og:title" content="{{ $pageTitle }}">
<meta property="og:description" content="{{ $pageDesc }}">
<meta property="og:url" content="{{ $pageUrl }}">
<meta name="twitter:card" content="summary">
@endpush

@section('content')

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Sora:wght@500;600;700&display=swap">

<script>
    // Scroll-reveal styles only apply when this runs and motion is allowed.
    if (!window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        document.documentElement.classList.add('js');
    }
</script>

<div class="home">

    {{-- HERO (fills the first screen) --}}
    @if($heroBanners->isNotEmpty())
        <div id="heroCarousel" class="carousel slide carousel-fade hero-fill" data-bs-ride="carousel" data-bs-interval="5000">
            @if($heroBanners->count() > 1)
                <div class="carousel-indicators">
                    @foreach($heroBanners as $banner)
                        <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="{{ $loop->index }}" class="{{ $loop->first ? 'active' : '' }}" aria-label="Slide {{ $loop->iteration }}"></button>
                    @endforeach
                </div>
            @endif

            <div class="carousel-inner h-100">
                @foreach($heroBanners as $banner)
                    {{-- one H1 per page for SEO: only the first slide uses h1 --}}
                    @php $headTag = $loop->first ? 'h1' : 'p'; @endphp
                    <div class="carousel-item h-100 {{ $loop->first ? 'active' : '' }}">
                        <div class="hero-slide text-white d-flex align-items-center">
                            <div class="hero-bg" style="background-image:url('{{ Storage::url($banner->image) }}');"></div>
                            <div class="hero-overlay"></div>
                            <div class="container position-relative text-center py-4">
                                <{{ $headTag }} class="display-4 fw-bold hero-anim">{{ $banner->title ?: \App\Models\Setting::get('hero_title', 'Enterprise IT Products & Solutions') }}</{{ $headTag }}>
                                <p class="lead text-white-50 hero-anim">{{ $banner->subtitle ?: \App\Models\Setting::get('hero_subtitle', 'For Businesses Across UAE') }}</p>
                                <div class="hero-anim">
                                    <a href="{{ route('products.index') }}" class="btn btn-brand btn-lg me-2">Explore Products</a>
                                    <a href="{{ $banner->link_url ?: route('quote-request.create') }}" class="btn btn-outline-light btn-lg">{{ $banner->button_text ?: 'Request a Quote' }}</a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @else
        <section class="hero-fill hero-fill-static text-white d-flex align-items-center" style="background:var(--ink);">
            <div class="container text-center py-4">
                <h1 class="display-4 fw-bold">{{ \App\Models\Setting::get('hero_title', 'Enterprise IT Products & Solutions') }}</h1>
                <p class="lead text-white-50">{{ \App\Models\Setting::get('hero_subtitle', 'For Businesses Across UAE') }}</p>
                <a href="{{ route('products.index') }}" class="btn btn-brand btn-lg me-2">Explore Products</a>
                <a href="{{ route('quote-request.create') }}" class="btn btn-outline-light btn-lg">Request a Quote</a>
            </div>
        </section>
    @endif

    <script>
        // Make the hero exactly fill the screen below the sticky header
        (function () {
            function setHeaderHeight() {
                var h = document.querySelector('.site-header');
                if (h) document.documentElement.style.setProperty('--header-h', h.offsetHeight + 'px');
            }
            setHeaderHeight();
            window.addEventListener('resize', setHeaderHeight);
            window.addEventListener('load', setHeaderHeight);
        })();
    </script>

    {{-- PROMO BANNERS --}}
    @if($promoBanners->isNotEmpty())
        <section class="container py-3 promo-strip">
            <div class="row justify-content-center">
                @foreach($promoBanners as $banner)
                    @php
                        // Real size of the uploaded image (falls back to 2:1 if it can't be read)
                        $file = Storage::disk('public')->path($banner->image);
                        $size = is_file($file) ? @getimagesize($file) : false;
                        $w = $size[0] ?? 800;
                        $h = $size[1] ?? 400;
                    @endphp
                    <div class="col-12 col-md-{{ 12 / min($promoBanners->count(), 3) }} mb-3">
                        <a href="{{ $banner->link_url ?: '#' }}"
                           class="d-block position-relative overflow-hidden rounded text-decoration-none">
                            <img src="{{ Storage::url($banner->image) }}"
                                 alt="{{ $banner->title ?? 'Promotion' }}"
                                 class="img-fluid w-100 d-block"
                                 width="{{ $w }}" height="{{ $h }}"
                                 loading="lazy" decoding="async"
                                 style="aspect-ratio: {{ $w }} / {{ $h }};">
                            @if($banner->title)
                                <div class="position-absolute bottom-0 start-0 w-100 p-3 text-white"
                                     style="background:linear-gradient(transparent, rgba(0,0,0,.65));">
                                    <div class="fw-semibold">{{ $banner->title }}</div>
                                </div>
                            @endif
                        </a>
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    {{-- TOP BRANDS: grid, max 5 per row --}}
    @if($featuredBrands->isNotEmpty())
    <section class="py-5" style="background:var(--brand-red-light);">
        <div class="container">
            <div class="section-eyebrow">Partners</div>
            <h2 class="section-title mb-4">Top Brands</h2>
            <div class="row row-cols-2 row-cols-sm-3 row-cols-md-5 g-3">
                @foreach($featuredBrands as $brand)
                    <div class="col">
                        <a href="{{ route('brands.show', $brand) }}" class="brand-tile bg-white w-100" style="height:90px;">
                            @if($brand->logo)
                                <img src="{{ Storage::url($brand->logo) }}" alt="{{ $brand->name }}">
                            @else
                                <span class="fw-semibold small text-center">{{ $brand->name }}</span>
                            @endif
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    {{-- BROWSE CATEGORIES: grid, max 5 per row --}}
    @if($featuredCategories->isNotEmpty())
    <section class="container py-5">
        <div class="section-eyebrow">Browse</div>
        <h2 class="section-title mb-4">Shop by Category</h2>
        <div class="row row-cols-3 row-cols-sm-4 row-cols-md-5 g-3">
            @foreach($featuredCategories as $category)
                <div class="col">
                    <a href="{{ route('categories.show', $category) }}" class="category-chip">
                        <div class="chip-icon">
                            @include('frontend.partials.category-icon', ['category' => $category, 'size' => 32, 'iconClass' => 'fs-5 text-danger'])
                        </div>
                        <div class="chip-label">{{ $category->name }}</div>
                    </a>
                </div>
            @endforeach
        </div>
    </section>
    @endif

    {{-- FEATURED PRODUCTS --}}
    @if($featuredProducts->isNotEmpty())
    <section class="container py-5">
        <div class="section-eyebrow">Top Picks</div>
        <h2 class="section-title mb-4">Featured Products</h2>
        <div class="row">
            @foreach($featuredProducts as $product)
                @include('frontend.partials.product-card', ['product' => $product])
            @endforeach
        </div>
    </section>
    @endif

    {{-- ============ NEW: everything below is added after the product listing ============ --}}

    {{-- NUMBERS --}}
    <section class="hm-stats">
        <div class="container">
            <dl class="hm-stats-grid">
                @foreach($facts as $f)
                    <div data-reveal style="--d: {{ $loop->index * .1 }}s">
                        <dt>{{ $f['label'] }}</dt>
                        <dd>
                            @if($f['to'] !== null)
                                {{ $f['pre'] }}<span class="hm-count" data-to="{{ $f['to'] }}">{{ number_format($f['to']) }}</span>{{ $f['suf'] }}
                            @else
                                {{ $f['raw'] }}
                            @endif
                        </dd>
                    </div>
                @endforeach
            </dl>
        </div>
    </section>

    {{-- FEATURE ROW 1: quote list --}}
    <section class="hm-sec">
        <div class="container">
            <div class="row g-5 align-items-center">
                <div class="col-lg-5" data-reveal>
                    <h2 class="hm-h2">Build one quote list across brands and categories</h2>
                    <p class="hm-lead">
                        Add servers, switches, laptops and accessories from different brands to a single list.
                        Set the quantities, send it, and get one quotation for the whole order.
                    </p>
                    <ul class="hm-ticks">
                        <li>Single items or bulk quantities</li>
                        <li>Products from every brand in one request</li>
                        <li>Pricing and availability from our team</li>
                    </ul>
                    <a href="{{ route('products.index') }}" class="btn btn-brand btn-lg">Start your quote list</a>
                </div>
                <div class="col-lg-7" data-reveal style="--d:.12s">
                    <figure class="hm-mock hm-tilt" role="img" aria-label="Example of a quote list with three products and quantities">
                        <div class="hm-mock-head"><span>Quote list</span><span class="hm-badge">{{ count($mockNames) }} items</span></div>
                        @foreach($mockNames as $i => $name)
                            <div class="hm-line" style="--d: {{ .35 + $i * .18 }}s">
                                <span class="hm-thumb"><i class="bi bi-box-seam"></i></span>
                                <span class="hm-line-name">{{ $name }}</span>
                                <span class="hm-qty"><span>−</span><b>{{ $mockQty[$i] ?? 1 }}</b><span>+</span></span>
                            </div>
                        @endforeach
                        <div class="hm-mock-foot"><span>Example quote list</span><span class="hm-fakebtn">Request a quote</span></div>
                    </figure>
                </div>
            </div>
        </div>
    </section>

    {{-- FEATURE ROW 2: catalogue --}}
    <section class="hm-sec hm-mist">
        <div class="container">
            <div class="row g-5 align-items-center">
                <div class="col-lg-7 order-lg-1 order-2" data-reveal>
                    <figure class="hm-mock hm-tilt hm-tilt-r" role="img" aria-label="Example of the catalogue organised by brand and category">
                        <div class="hm-mock-head"><span>Catalogue</span><span class="hm-badge">Brands and categories</span></div>
                        @if($featuredBrands->isNotEmpty())
                            <div class="hm-group">
                                <div class="hm-group-title">Brands</div>
                                <div class="hm-chips">
                                    @foreach($featuredBrands->take(6) as $brand)
                                        <span class="hm-chip">{{ $brand->name }}</span>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                        <div class="hm-group">
                            <div class="hm-group-title">Categories</div>
                            <div class="hm-chips">
                                @forelse($featuredCategories->take(6) as $category)
                                    <span class="hm-chip">{{ $category->name }}</span>
                                @empty
                                    @foreach(['Servers', 'Storage', 'Networking', 'Laptops', 'Peripherals'] as $n)
                                        <span class="hm-chip">{{ $n }}</span>
                                    @endforeach
                                @endforelse
                            </div>
                        </div>
                    </figure>
                </div>
                <div class="col-lg-5 order-lg-2 order-1" data-reveal style="--d:.12s">
                    <h2 class="hm-h2">Find IT products by brand or category</h2>
                    <p class="hm-lead">
                        The catalogue is organised by brand and category, so IT managers and resellers can reach the
                        right product line quickly. If a model is missing, send us the part number and we check
                        whether we can supply it.
                    </p>
                    <ul class="hm-ticks">
                        <li>Browse by brand</li>
                        <li>Browse by category</li>
                        <li>Ask us for items that are not listed</li>
                    </ul>
                    <a href="{{ route('products.index') }}" class="btn btn-outline-brand btn-lg">Browse the catalogue</a>
                </div>
            </div>
        </div>
    </section>

    {{-- FEATURE ROW 3: delivery --}}
    <section class="hm-sec">
        <div class="container">
            <div class="row g-5 align-items-center">
                <div class="col-lg-5" data-reveal>
                    <h2 class="hm-h2">Delivery across the UAE, with warranty support</h2>
                    <p class="hm-lead">
                        Orders are dispatched promptly to your office or site. Products carry the manufacturer’s
                        warranty, and our team answers technical questions before and after you order.
                    </p>
                    <ul class="hm-ticks">
                        <li>Dispatch from Dubai to your site in the UAE</li>
                        <li>Genuine products from authorised channels</li>
                        <li>One contact from quotation to delivery</li>
                    </ul>
                    <a href="{{ $aboutUrl }}" class="btn btn-outline-brand btn-lg">About {{ $company }}</a>
                </div>
                <div class="col-lg-7" data-reveal style="--d:.12s">
                    @if($deliveryImg)
                        <div class="hm-photo"><img src="{{ $deliveryImg }}" width="960" height="720" loading="lazy" decoding="async" alt="IT equipment ready for delivery from {{ $company }}"></div>
                    @else
                        {{-- Fallback illustration: route from Dubai to a customer site --}}
                        <div class="hm-photo hm-art">
                            <svg viewBox="0 0 560 380" role="img" aria-label="Illustration of a delivery route from Dubai to a customer site">
                                @foreach(range(1, 8) as $g)
                                    <line x1="{{ $g * 62 }}" y1="0" x2="{{ $g * 62 }}" y2="380" stroke="#e2e6ea"/>
                                @endforeach
                                @foreach(range(1, 5) as $g)
                                    <line x1="0" y1="{{ $g * 62 }}" x2="560" y2="{{ $g * 62 }}" stroke="#e2e6ea"/>
                                @endforeach
                                <path d="M90 300 C190 300 190 160 300 175 S440 95 475 80" fill="none" stroke="#cfd5db" stroke-width="3" stroke-linecap="round"/>
                                <path class="hm-route" d="M90 300 C190 300 190 160 300 175 S440 95 475 80" fill="none" style="stroke:var(--brand-red,#e4002b)" stroke-width="3" stroke-linecap="round" stroke-dasharray="8 10"/>
                                <circle cx="90" cy="300" r="11" fill="#0b1017"/>
                                <circle cx="90" cy="300" r="4" fill="#fff"/>
                                <circle class="hm-pulse" cx="475" cy="80" r="11" style="fill:var(--brand-red,#e4002b)"/>
                                <circle cx="475" cy="80" r="4" fill="#fff"/>
                                <text x="108" y="336" font-size="15" font-weight="600" fill="#0b1017">Dubai</text>
                                <text x="395" y="58" font-size="15" font-weight="600" fill="#0b1017">Your site</text>
                            </svg>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>

    {{-- WHO WE SUPPLY --}}
    <section class="hm-sec hm-dark">
        <div class="container">
            <div class="hm-head" data-reveal>
                <h2 class="hm-h2">IT supply for companies, government bodies and resellers</h2>
                <p class="hm-lead mb-0">Whatever the size of the order, the process is the same: send a list and get a quotation.</p>
            </div>
            <div class="row g-4">
                @foreach($buyers as [$icon, $title, $text, $cta])
                    <div class="col-md-4" data-reveal style="--d: {{ $loop->index * .12 }}s">
                        <div class="hm-buyer">
                            <i class="bi {{ $icon }} hm-buyer-ico"></i>
                            <h3>{{ $title }}</h3>
                            <p>{{ $text }}</p>
                            <a href="{{ route('quote-request.create') }}" class="hm-link">{{ $cta }}</a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- WHY BUY FROM US (replaces the old trust badges) --}}
    <section class="hm-sec">
        <div class="container">
            <div class="hm-head" data-reveal>
                <h2 class="hm-h2">Why buy IT products from {{ $company }}</h2>
            </div>
            <div class="row g-3">
                @foreach($why as [$icon, $title, $text])
                    <div class="col-md-6 col-lg-4" data-reveal style="--d: {{ ($loop->index % 3) * .1 }}s">
                        <div class="hm-why">
                            <i class="bi {{ $icon }} hm-why-ico"></i>
                            <h3>{{ $title }}</h3>
                            <p>{{ $text }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- FAQ --}}
    <section class="hm-sec hm-mist">
        <div class="container">
            <div class="row g-5">
                <div class="col-lg-4" data-reveal>
                    <h2 class="hm-h2">Questions about ordering IT equipment in Dubai</h2>
                    <p class="hm-lead">Cannot find your answer? <a href="{{ route('contact.create') }}" class="hm-link">Contact our team</a>.</p>
                </div>
                <div class="col-lg-8" data-reveal style="--d:.1s">
                    <div class="hm-faq">
                        @foreach($faqs as [$q, $a])
                            <details>
                                <summary>{{ $q }}</summary>
                                <p>{{ $a }}</p>
                            </details>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- CTA BAND --}}
    <section class="hm-cta">
        <div class="container">
            <div class="row g-5 align-items-center">
                <div class="col-lg-7" data-reveal>
                    <h2 class="hm-h2">Looking for a specific IT product?</h2>
                    <p class="hm-lead mb-4">
                        Send the model or part number with the quantity you need. Our team replies with pricing and availability.
                    </p>
                    <a href="{{ route('quote-request.create') }}" class="btn btn-brand btn-lg me-2 mb-2">Request a Quote</a>
                    <a href="{{ route('products.index') }}" class="btn btn-outline-light btn-lg mb-2">Browse products</a>
                </div>
                <div class="col-lg-5" data-reveal style="--d:.12s">
                    <a class="hm-contact" href="mailto:{{ $email }}"><i class="bi bi-envelope"></i><span><small>Email</small>{{ $email }}</span></a>
                    <a class="hm-contact" href="tel:{{ $tel }}"><i class="bi bi-telephone"></i><span><small>Phone</small>{{ $phone }}</span></a>
                </div>
            </div>
        </div>
    </section>

</div>

<script type="application/ld+json">{!! json_encode($graph, $jsonFlags) !!}</script>

<script>
    (function () {
        var root = document.documentElement;
        var reveals = document.querySelectorAll('.home [data-reveal]');
        var counts = document.querySelectorAll('.hm-count');

        if (!root.classList.contains('js') || !('IntersectionObserver' in window)) {
            reveals.forEach(function (el) { el.classList.add('in'); });
            return;
        }

        var io = new IntersectionObserver(function (entries) {
            entries.forEach(function (e) {
                if (e.isIntersecting) { e.target.classList.add('in'); io.unobserve(e.target); }
            });
        }, { threshold: 0.15, rootMargin: '0px 0px -6% 0px' });
        reveals.forEach(function (el) { io.observe(el); });

        var co = new IntersectionObserver(function (entries) {
            entries.forEach(function (e) {
                if (!e.isIntersecting) return;
                co.unobserve(e.target);
                var el = e.target, to = parseInt(el.getAttribute('data-to'), 10), start = null, dur = 1400;
                function tick(t) {
                    if (start === null) start = t;
                    var p = Math.min((t - start) / dur, 1);
                    el.textContent = Math.round(to * (1 - Math.pow(1 - p, 3))).toLocaleString('en-US');
                    if (p < 1) requestAnimationFrame(tick);
                }
                requestAnimationFrame(tick);
            });
        }, { threshold: 0.6 });
        counts.forEach(function (el) { co.observe(el); });
    })();
</script>

@endsection