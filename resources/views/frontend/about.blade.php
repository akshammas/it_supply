@php
    $company = \App\Models\Setting::get('company_name', config('app.name'));
    $address = \App\Models\Setting::get('address', 'UAE | Dubai');
    $email   = \App\Models\Setting::get('email', 'info@example.com');
    $phone   = \App\Models\Setting::get('phone', '+971 00 000 0000');
    $tel     = preg_replace('/[^\d+]/', '', $phone);

    $pageTitle = 'About ' . $company . ' | IT Supplier in Dubai, UAE';
    $pageDesc  = \Illuminate\Support\Str::limit(
        $company . ' is an IT supplier in Dubai. Genuine servers, storage, networking, laptops and peripherals for businesses, government bodies and resellers across the UAE.',
        158
    );

    // Photos: put files in public/images/about/ (hero.jpg, story.jpg, gallery-1.jpg, gallery-2.jpg, gallery-3.jpg).
    // hero and story fall back to SVG illustrations; the gallery only shows when its files exist.
    $img      = fn ($f) => file_exists(public_path("images/about/$f")) ? asset("images/about/$f") : null;
    $heroImg  = $img('hero.jpg');
    $storyImg = $img('story.jpg');
    $gallery  = array_values(array_filter([$img('gallery-1.jpg'), $img('gallery-2.jpg'), $img('gallery-3.jpg')]));

    $brands = $brands ?? (class_exists(\App\Models\Brand::class)
        ? \App\Models\Brand::orderBy('name')->limit(20)->get()
        : collect());
    $reps = $brands->count() ? (int) ceil(8 / $brands->count()) : 0;

    // Figures are editable in Settings. Check them before launch.
    $facts = [];
    foreach ([
        [\App\Models\Setting::get('about_years', '10+'),      'Years in the market'],
        [\App\Models\Setting::get('about_brands', '50+'),     'Global brands'],
        [\App\Models\Setting::get('about_products', '1000+'), 'Products available'],
        [\App\Models\Setting::get('about_customers', '500+'), 'Business customers'],
    ] as [$raw, $label]) {
        $ok = preg_match('/^(\D*)([\d,]+)(.*)$/', (string) $raw, $m);
        $facts[] = [
            'pre' => $ok ? $m[1] : '',
            'to'  => $ok ? (int) str_replace(',', '', $m[2]) : null,
            'suf' => $ok ? $m[3] : '',
            'raw' => $raw,
            'label' => $label,
        ];
    }

    $datasheet = [
        ['Based in',    'Dubai, UAE'],
        ['Customers',   'Companies, government bodies and IT resellers'],
        ['Supplies',    'Servers, storage, networking, laptops and peripherals'],
        ['Order size',  'Single items up to large project orders'],
        ['Delivery',    'Across the UAE'],
    ];

    $cats = [
        ['bi-hdd-rack',  'Servers and workstations', 'Rack and tower servers, workstations and server accessories.'],
        ['bi-device-hdd','Storage',                  'NAS and external storage, drives and backup hardware.'],
        ['bi-router',    'Networking',               'Switches, routers and wireless equipment for offices of every size.'],
        ['bi-shield-lock','Network security',        'Firewalls and other security equipment.'],
        ['bi-laptop',    'Laptops and desktops',     'Business laptops and desktop computers for staff and branches.'],
        ['bi-printer',   'Peripherals',              'Monitors, printers, keyboards, mice and accessories.'],
    ];

    $serve = [
        ['bi-building', 'Companies',         'IT teams and office managers buying equipment for staff, branches and new sites. One contact handles the order from quotation to delivery.'],
        ['bi-bank',     'Government bodies', 'Equipment sourced through written quotations, with genuine products and manufacturer warranty.'],
        ['bi-shop',     'IT resellers',      'Stock for your own customers, with quotations for bulk orders.'],
    ];

    $steps = [
        ['Choose products',     'Browse the catalogue and add items to your quote list.'],
        ['Send the list',       'Submit it with your name, company and contact details.'],
        ['Get a quotation',     'Our team replies with pricing and availability.'],
        ['Confirm and receive', 'Once you confirm, we dispatch across the UAE.'],
    ];

    $commitments = [
        ['bi-patch-check', 'Genuine products',        'Products come through manufacturers and authorised distributors, with the manufacturer’s warranty.'],
        ['bi-cash-stack',  'Competitive pricing',     'Buying from suppliers directly keeps costs down, and quotations cover single items as well as bulk orders.'],
        ['bi-truck',       'Delivery across the UAE', 'Orders are dispatched promptly to your office or site.'],
        ['bi-headset',     'Product advice',          'Our staff can help you compare models and choose equipment that fits the job.'],
        ['bi-person-lines-fill', 'One point of contact', 'The same team handles your enquiry, quotation and delivery.'],
        ['bi-receipt',     'Quotations for any size', 'Ask for one laptop or a full project list. We quote both.'],
    ];

    $faqs = [
        ['Are your products genuine?',
         'Yes. We buy from manufacturers and authorised distributors, so products are genuine.'],
        ['Do products come with a warranty?',
         'Products carry the manufacturer’s warranty. Contact us if you need warranty details for a specific model.'],
        ['Do you deliver outside Dubai?',
         'We deliver across the UAE. Send us your location and we will confirm delivery details with your quotation.'],
        ['Can I order a single item, or only in bulk?',
         'Both. We quote single items as well as large project orders.'],
        ['How do I get a quotation?',
         'Add products to your quote list from the catalogue, or contact us with your list and quantities. Our team replies with pricing and availability.'],
        ['Do you supply government bodies and resellers?',
         'Yes. Companies, government bodies and IT resellers all buy from us.'],
        ['What if I cannot find a product in the catalogue?',
         'Send us the model or part number and we will check whether we can supply it.'],
        ['Which brands do you supply?',
         'The catalogue shows the brands we supply. If you need a specific brand or model, ask our team.'],
    ];

    $pageUrl = url()->current();
    $graph = [
        '@context' => 'https://schema.org',
        '@graph' => [
            array_filter([
                '@type'     => 'LocalBusiness',
                '@id'       => url('/') . '#organization',
                'name'      => $company,
                'url'       => url('/'),
                'description' => $pageDesc,
                'email'     => $email,
                'telephone' => $phone,
                'image'     => $heroImg,
                'address'   => ['@type' => 'PostalAddress', 'streetAddress' => $address, 'addressLocality' => 'Dubai', 'addressCountry' => 'AE'],
                'areaServed' => ['@type' => 'Country', 'name' => 'United Arab Emirates'],
                'knowsAbout' => ['Servers', 'Storage', 'Networking equipment', 'Network security', 'Laptops and desktops', 'IT peripherals'],
                'hasOfferCatalog' => [
                    '@type' => 'OfferCatalog',
                    'name'  => 'IT products',
                    'itemListElement' => array_map(fn ($c) => ['@type' => 'OfferCatalog', 'name' => $c[1], 'description' => $c[2]], $cats),
                ],
            ]),
            [
                '@type' => 'AboutPage',
                '@id'   => $pageUrl . '#webpage',
                'url'   => $pageUrl,
                'name'  => $pageTitle,
                'description' => $pageDesc,
                'about' => ['@id' => url('/') . '#organization'],
                'breadcrumb' => ['@id' => $pageUrl . '#breadcrumb'],
            ],
            [
                '@type' => 'BreadcrumbList',
                '@id'   => $pageUrl . '#breadcrumb',
                'itemListElement' => [
                    ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home',     'item' => url('/')],
                    ['@type' => 'ListItem', 'position' => 2, 'name' => 'About us', 'item' => $pageUrl],
                ],
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
@if($heroImg)<meta property="og:image" content="{{ $heroImg }}">@endif
<meta name="twitter:card" content="{{ $heroImg ? 'summary_large_image' : 'summary' }}">
<meta name="twitter:title" content="{{ $pageTitle }}">
<meta name="twitter:description" content="{{ $pageDesc }}">
@endpush

@section('content')



<script>
    // Scroll-reveal styles only apply when this runs and motion is allowed.
    if (!window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        document.documentElement.classList.add('js');
    }
</script>



<div class="about">

    {{-- Hero --}}
    <section class="hero dark">
        <div class="container">
            <nav class="crumbs" aria-label="Breadcrumb"><a href="{{ url('/') }}">Home</a> / About us</nav>

            <div class="row align-items-center g-5">
                <div class="col-lg-6 hero-in">
                    <h1 style="--i:0">Enterprise IT hardware and networking supplier in Dubai</h1>
                    <p class="lede mb-4" style="--i:1">
                        {{ $company }} supplies genuine servers, storage, networking equipment, laptops and
                        peripherals to businesses, government bodies and IT resellers across the UAE.
                        Send us your list and we reply with a quotation.
                    </p>
                    <div style="--i:2">
                        <a href="{{ route('contact.create') }}" class="btn btn-brand btn-lg me-2 mb-2">Request a quotation</a>
                        <a href="{{ route('products.index') }}" class="btn btn-outline-light btn-lg mb-2">Browse IT products</a>
                    </div>
                </div>

                <div class="col-lg-6 hero-in">
                    <div class="frame" style="--i:2">
                        @if($heroImg)
                            <img src="{{ $heroImg }}" width="960" height="768" alt="IT equipment supplied by {{ $company }} in Dubai" fetchpriority="high" decoding="async">
                        @else
                            {{-- Fallback illustration: server rack --}}
                            <div class="art" style="background:#0e141b;">
                                <svg viewBox="0 0 520 420" role="img" aria-label="Server rack illustration">
                                    <path d="M170 380 C90 380 90 300 40 300" fill="none" style="stroke:var(--brand-red,#e4002b)" stroke-width="5" stroke-linecap="round"/>
                                    <path d="M350 330 C430 330 430 250 490 250" fill="none" stroke="#3a4655" stroke-width="5" stroke-linecap="round"/>
                                    <rect x="170" y="22" width="180" height="376" rx="8" fill="#10161e" stroke="#2b3644" stroke-width="2"/>
                                    @foreach(range(0, 6) as $i)
                                        @php $y = 38 + $i * 50; @endphp
                                        <rect x="182" y="{{ $y }}" width="156" height="42" rx="3" fill="#18212b" stroke="#2b3644"/>
                                        <circle class="led" cx="194" cy="{{ $y + 21 }}" r="3" style="fill:var(--brand-red,#e4002b);animation-delay:{{ $i * .37 }}s"/>
                                        <circle class="led" cx="206" cy="{{ $y + 21 }}" r="3" fill="#cdd6df" style="animation-delay:{{ $i * .53 }}s"/>
                                        @foreach(range(0, 6) as $p)
                                            <rect x="{{ 226 + $p * 15 }}" y="{{ $y + 15 }}" width="10" height="12" rx="1.5" fill="#3a4655"/>
                                        @endforeach
                                    @endforeach
                                </svg>
                            </div>
                        @endif
                        <span class="chip a"><i class="bi bi-patch-check"></i> Genuine products</span>
                        <span class="chip b"><i class="bi bi-shield-check"></i> Manufacturer warranty</span>
                    </div>
                </div>
            </div>

            <dl class="stats">
                @foreach($facts as $f)
                    <div data-reveal style="--d: {{ $loop->index * .1 }}s">
                        <dt>{{ $f['label'] }}</dt>
                        <dd>
                            @if($f['to'] !== null)
                                {{ $f['pre'] }}<span class="count" data-to="{{ $f['to'] }}">{{ number_format($f['to']) }}</span>{{ $f['suf'] }}
                            @else
                                {{ $f['raw'] }}
                            @endif
                        </dd>
                    </div>
                @endforeach
            </dl>
        </div>
    </section>

    {{-- Brands --}}
    @if($brands->count())
        <section class="brands" aria-label="Brands in our catalogue">
            <div class="container"><p class="brands-title">Brands in our catalogue</p></div>
            <div class="marquee">
                <div class="marquee-track">
                    @for($half = 0; $half < 2; $half++)
                        <ul @if($half) aria-hidden="true" @endif>
                            @for($r = 0; $r < $reps; $r++)
                                @foreach($brands as $brand)<li>{{ $brand->name }}</li>@endforeach
                            @endfor
                        </ul>
                    @endfor
                </div>
            </div>
        </section>
    @endif

    {{-- Story --}}
    <section class="sec">
        <div class="container">
            <div class="row g-5 align-items-center">
                <div class="col-lg-5" data-reveal>
                    <div class="story-img">
                        @if($storyImg)
                            <img src="{{ $storyImg }}" width="960" height="720" loading="lazy" decoding="async" alt="Products prepared for dispatch by {{ $company }}">
                        @else
                            {{-- Fallback illustration: network switch with patch cables --}}
                            <div class="art">
                                <svg viewBox="0 0 480 360" role="img" aria-label="Network switch illustration">
                                    <rect x="40" y="110" width="400" height="96" rx="6" fill="#1b232d"/>
                                    @foreach(range(0, 11) as $p)
                                        <rect x="{{ 64 + $p * 30 }}" y="128" width="20" height="22" rx="2" fill="#4b5563"/>
                                        <rect x="{{ 64 + $p * 30 }}" y="162" width="20" height="22" rx="2" fill="#4b5563"/>
                                    @endforeach
                                    <circle class="led" cx="420" cy="124" r="3.5" fill="#d7dde4"/>
                                    <circle class="led" cx="420" cy="136" r="3.5" style="fill:var(--brand-red,#e4002b);animation-delay:.8s"/>
                                    <path d="M74 150 C74 250 160 250 160 296" fill="none" style="stroke:var(--brand-red,#e4002b)" stroke-width="5" stroke-linecap="round"/>
                                    <path d="M134 184 C134 260 250 240 250 296" fill="none" stroke="#9aa1ab" stroke-width="5" stroke-linecap="round"/>
                                    <path d="M254 150 C254 230 340 250 340 296" fill="none" stroke="#1b232d" stroke-width="5" stroke-linecap="round"/>
                                    <path d="M374 184 C374 240 410 250 410 296" fill="none" stroke="#9aa1ab" stroke-width="5" stroke-linecap="round"/>
                                </svg>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="col-lg-7" data-reveal style="--d:.12s">
                    <h2>A Dubai IT supplier that buys at source</h2>
                    <p>
                        {{ $company }} is an IT supplier based in Dubai. Our customers are companies, government
                        bodies and IT resellers who need servers, storage, networking equipment, laptops and
                        peripherals, as single units or by the project.
                    </p>
                    <p>
                        We work directly with manufacturers and authorised distributors. That is how we offer genuine
                        products with a proper manufacturer warranty, and why our prices stay competitive.
                    </p>
                    <p class="mb-0">
                        Each order has one point of contact, from the first quotation to delivery. If you are unsure
                        which model fits, ask our team before you order.
                    </p>
                    <dl class="datasheet">
                        @foreach($datasheet as [$k, $v])
                            <div><dt>{{ $k }}</dt><dd>{{ $v }}</dd></div>
                        @endforeach
                    </dl>
                </div>
            </div>
        </div>
    </section>

    {{-- What we supply --}}
    <section class="sec mist">
        <div class="container">
            <div class="sec-head" data-reveal>
                <h2>IT products we supply in the UAE</h2>
                <p class="mb-0">
                    The catalogue covers the equipment an office, branch or server room runs on. If a model is not
                    listed, send us the part number.
                </p>
            </div>
            <div class="row g-4">
                @foreach($cats as [$icon, $title, $text])
                    <div class="col-md-6 col-lg-4" data-reveal style="--d: {{ ($loop->index % 3) * .1 }}s">
                        <article class="cat">
                            <span class="ico"><i class="bi {{ $icon }}"></i></span>
                            <h3>{{ $title }}</h3>
                            <p>{{ $text }}</p>
                        </article>
                    </div>
                @endforeach
            </div>
            <div class="mt-5" data-reveal>
                <a href="{{ route('products.index') }}" class="btn btn-brand btn-lg">Browse all IT products</a>
            </div>
        </div>
    </section>

    {{-- Who we supply --}}
    <section class="sec dark  hm-grid">
        <div class="container">
            <div class="sec-head" data-reveal>
                <h2>Who we supply</h2>
                <p class="mb-0">Three kinds of buyer make up most of our orders.</p>
            </div>
            <div class="row g-4">
                @foreach($serve as [$icon, $title, $text])
                    <div class="col-md-4" data-reveal style="--d: {{ $loop->index * .12 }}s">
                        <div class="aud">
                            <i class="bi {{ $icon }} ico"></i>
                            <h3>{{ $title }}</h3>
                            <p>{{ $text }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Process --}}
    <section class="sec">
        <div class="container">
            <div class="sec-head" data-reveal>
                <h2>How to get a quotation</h2>
            </div>
            <ol class="steps" data-reveal>
                @foreach($steps as [$title, $text])
                    <li style="--d: {{ $loop->index * .18 }}s"><strong>{{ $title }}</strong><span>{{ $text }}</span></li>
                @endforeach
            </ol>
        </div>
    </section>

    {{-- Commitments --}}
    <section class="sec mist">
        <div class="container">
            <div class="sec-head" data-reveal>
                <h2>What you get when you order from {{ $company }}</h2>
            </div>
            <div class="row g-3">
                @foreach($commitments as [$icon, $title, $text])
                    <div class="col-md-6 col-lg-4" data-reveal style="--d: {{ ($loop->index % 3) * .1 }}s">
                        <div class="commit">
                            <i class="bi {{ $icon }} ico"></i>
                            <h3>{{ $title }}</h3>
                            <p>{{ $text }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Gallery: appears when gallery-1..3.jpg exist --}}
    @if(count($gallery))
        <section class="sec">
            <div class="container">
                <div class="sec-head" data-reveal><h2>Gallery</h2></div>
                <div class="row g-3">
                    @foreach($gallery as $i => $src)
                        <div class="col-md-4" data-reveal style="--d: {{ $i * .1 }}s">
                            <span class="gal"><img src="{{ $src }}" width="800" height="600" loading="lazy" decoding="async" alt="{{ $company }} gallery photo {{ $i + 1 }}"></span>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- FAQ --}}
    <section class="sec {{ count($gallery) ? 'mist' : '' }}">
        <div class="container">
            <div class="row g-5">
                <div class="col-lg-4" data-reveal>
                    <h2>Questions about ordering IT equipment in Dubai</h2>
                    <p>Cannot find your answer? <a href="{{ route('contact.create') }}" style="color:var(--red);font-weight:600;">Contact our team</a>.</p>
                </div>
                <div class="col-lg-8" data-reveal style="--d:.1s">
                    <div class="faq">
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

    {{-- Contact --}}
    <section class="sec dark cta">
        <div class="container">
            <div class="row g-5 align-items-center">
                <div class="col-lg-6" data-reveal>
                    <h2>Request a quotation</h2>
                    <p class="mb-4">Send your product list and quantities. Our team replies with pricing and availability.</p>
                    <a href="{{ route('contact.create') }}" class="btn btn-brand btn-lg me-2 mb-2">Request a quotation</a>
                    <a href="{{ route('products.index') }}" class="btn btn-outline-light btn-lg mb-2">Browse IT products</a>
                </div>
                <div class="col-lg-6" data-reveal style="--d:.12s">
                    <div class="row g-3">
                        <div class="col-12">
                            <div class="contact"><i class="bi bi-geo-alt"></i><div><small>Address</small><span>{{ $address }}</span></div></div>
                        </div>
                        <div class="col-sm-6">
                            <a class="contact" href="mailto:{{ $email }}"><i class="bi bi-envelope"></i><div><small>Email</small><span>{{ $email }}</span></div></a>
                        </div>
                        <div class="col-sm-6">
                            <a class="contact" href="tel:{{ $tel }}"><i class="bi bi-telephone"></i><div><small>Phone</small><span>{{ $phone }}</span></div></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

</div>

<script type="application/ld+json">{!! json_encode($graph, $jsonFlags) !!}</script>

<script>
    (function () {
        var root = document.documentElement;
        var reveals = document.querySelectorAll('[data-reveal]');
        var counts = document.querySelectorAll('.count');

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