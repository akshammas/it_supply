@php
    $company  = \App\Models\Setting::get('company_name', config('app.name'));
    $address  = \App\Models\Setting::get('address', 'UAE | Dubai');
    $email    = \App\Models\Setting::get('email');
    $phone    = \App\Models\Setting::get('phone');
    $waNumber = preg_replace('/\D/', '', (string) \App\Models\Setting::get('whatsapp_number'));
    $waLink   = $waNumber ? 'https://wa.me/'.$waNumber.'?text='.rawurlencode("Hello {$company}, I would like to enquire about your products.") : null;
    $telLink  = $phone ? 'tel:'.preg_replace('/[^\d+]/', '', $phone) : null;
    $mapsUrl  = \App\Models\Setting::get('google_maps_url') ?: 'https://www.google.com/maps/search/?api=1&query='.urlencode($address);
    $mapEmbed = 'https://www.google.com/maps?q='.urlencode($address).'&output=embed';
    $socials  = array_filter([
        ['bi-facebook',  'Facebook',  \App\Models\Setting::get('facebook_url')],
        ['bi-instagram', 'Instagram', \App\Models\Setting::get('instagram_url')],
        ['bi-linkedin',  'LinkedIn',  \App\Models\Setting::get('linkedin_url')],
    ], fn ($s) => !empty($s[2]));

    $pageTitle = 'Contact Us | IT Supplier in Dubai | ' . config('app.name');
    $pageDesc  = \Illuminate\Support\Str::limit(
        'Contact ' . $company . ' in Dubai for IT product enquiries, pricing and project quotations. Send a message, call, email or chat on WhatsApp.',
        158
    );
    $pageUrl = url()->current();

    $tips = [
        'Product model or part number',
        'Quantity for each item',
        'Delivery location in the UAE',
        'Company name and contact person',
    ];

    $steps = [
        ['We read your message',   'Our team reviews your request and checks product availability.'],
        ['You get a reply',        'We reply with pricing and availability, or with answers to your questions.'],
        ['Confirm and receive',    'Once you confirm an order, we dispatch it across the UAE.'],
    ];

    $faqs = [
        ['How do I get a quotation?', 'Send us your list and quantities using the form on this page, or use the quote request page. Our team replies with pricing and availability.'],
        ['Can I send a list of products?', 'Yes. Include model or part numbers and quantities, and we will quote the whole list.'],
        ['Do you deliver outside Dubai?', 'We deliver across the UAE. Tell us your location and we will confirm delivery details with your quotation.'],
        ['Do you supply government bodies and resellers?', 'Yes. Companies, government bodies and IT resellers all buy from us.'],
        ['What if I cannot find a product in the catalogue?', 'Send us the model or part number and we will check whether we can supply it.'],
    ];

    $graph = [
        '@context' => 'https://schema.org',
        '@graph' => [
            [
                '@type' => 'ContactPage',
                '@id'   => $pageUrl . '#webpage',
                'url'   => $pageUrl,
                'name'  => $pageTitle,
                'description' => $pageDesc,
                'about' => ['@id' => url('/') . '#organization'],
                'breadcrumb' => ['@id' => $pageUrl . '#breadcrumb'],
            ],
            array_filter([
                '@type' => 'LocalBusiness',
                '@id'   => url('/') . '#organization',
                'name'  => $company,
                'url'   => url('/'),
                'email' => $email,
                'telephone' => $phone,
                'address' => ['@type' => 'PostalAddress', 'streetAddress' => $address, 'addressLocality' => 'Dubai', 'addressCountry' => 'AE'],
                'areaServed' => ['@type' => 'Country', 'name' => 'United Arab Emirates'],
                'sameAs' => array_values(array_map(fn ($s) => $s[2], $socials)),
                'contactPoint' => ($phone || $email) ? [array_filter([
                    '@type' => 'ContactPoint',
                    'contactType' => 'sales',
                    'telephone' => $phone,
                    'email' => $email,
                    'areaServed' => 'AE',
                ])] : null,
            ]),
            [
                '@type' => 'BreadcrumbList',
                '@id'   => $pageUrl . '#breadcrumb',
                'itemListElement' => [
                    ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => url('/')],
                    ['@type' => 'ListItem', 'position' => 2, 'name' => 'Contact us', 'item' => $pageUrl],
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
<meta name="twitter:card" content="summary">
@endpush

@section('content')



<script>
    if (!window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        document.documentElement.classList.add('js');
    }
</script>

<div class="contact-page">

    {{-- Header --}}
    <section class="contact-hero">
        <div class="container ct-in">
            <nav class="ct-crumbs" aria-label="Breadcrumb" style="--i:0"><a href="{{ url('/') }}">Home</a> / Contact us</nav>
            <h1 style="--i:1">Contact our IT supply team in Dubai</h1>
            <p class="lead" style="--i:2">Questions about products, pricing or a project? Send us a message, call, or chat with us on WhatsApp.</p>
            <div style="--i:3">
                <a href="#contact-form" class="btn btn-brand btn-lg me-2 mb-2">Send a message</a>
                <a href="{{ route('quote-request.create') }}" class="btn btn-outline-light btn-lg mb-2">Request a Quote</a>
            </div>
        </div>
    </section>

    {{-- Contact cards --}}
    <section class="container contact-cards">
        <div class="row g-3">
            <div class="col-sm-6 col-lg" data-reveal>
                <div class="contact-card">
                    <div class="contact-icon"><i class="bi bi-geo-alt"></i></div>
                    <h2>Visit us</h2>
                    <p>{{ $address }}</p>
                    <a href="{{ $mapsUrl }}" target="_blank" rel="noopener" class="contact-link stretched-link">Get directions</a>
                </div>
            </div>

            @if($phone)
                <div class="col-sm-6 col-lg" data-reveal style="--d:.08s">
                    <div class="contact-card">
                        <div class="contact-icon"><i class="bi bi-telephone"></i></div>
                        <h2>Call us</h2>
                        <p>{{ $phone }}</p>
                        <a href="{{ $telLink }}" class="contact-link stretched-link">Call now</a>
                    </div>
                </div>
            @endif

            @if($waLink)
                <div class="col-sm-6 col-lg" data-reveal style="--d:.16s">
                    <div class="contact-card">
                        <div class="contact-icon"><i class="bi bi-whatsapp"></i></div>
                        <h2>WhatsApp</h2>
                        <p>Chat with our team directly.</p>
                        <a href="{{ $waLink }}" target="_blank" rel="noopener" class="contact-link stretched-link">Start chat</a>
                    </div>
                </div>
            @endif

            @if($email)
                <div class="col-sm-6 col-lg" data-reveal style="--d:.24s">
                    <div class="contact-card">
                        <div class="contact-icon"><i class="bi bi-envelope"></i></div>
                        <h2>Email us</h2>
                        <p>{{ $email }}</p>
                        <a href="mailto:{{ $email }}" class="contact-link stretched-link">Send email</a>
                    </div>
                </div>
            @endif
        </div>
    </section>

    {{-- Form + sidebar --}}
    <section class="container ct-sec">
        <div class="row g-4 g-lg-5">
            <div class="col-lg-7" data-reveal>
                <div class="contact-form-card" id="contact-form">
                    <h2>Send us a message</h2>
                    <p class="text-muted mb-3">Fill in the form and we will get back to you shortly.</p>

                    @if(collect($enquiryTypes)->isNotEmpty())
                        <div class="ct-chips" aria-label="Topics we can help with">
                            @foreach($enquiryTypes as $type)<span>{{ $type }}</span>@endforeach
                        </div>
                    @endif

                    @if(session('status'))
                        <div class="alert alert-success d-flex align-items-center gap-2" role="alert">
                            <i class="bi bi-check-circle fs-5"></i><div>{{ session('status') }}</div>
                        </div>
                    @endif
                    @if($errors->any())
                        <div class="alert alert-danger d-flex align-items-center gap-2" role="alert">
                            <i class="bi bi-exclamation-triangle fs-5"></i><div>Please check the highlighted fields and try again.</div>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('contact.store') }}" id="contactForm">
                        @csrf
                        @if(!empty($fromQuote) || old('from_quote'))
                            <input type="hidden" name="from_quote" value="1">
                        @endif
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="text" name="name" id="cName" value="{{ old('name') }}" placeholder="Full name" autocomplete="name"
                                           class="form-control @error('name') is-invalid @enderror" required>
                                    <label for="cName">Full name *</label>
                                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="text" name="company_name" id="cCompany" value="{{ old('company_name') }}" placeholder="Company" autocomplete="organization"
                                           class="form-control @error('company_name') is-invalid @enderror">
                                    <label for="cCompany">Company name</label>
                                    @error('company_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="email" name="email" id="cEmail" value="{{ old('email') }}" placeholder="Email" autocomplete="email"
                                           class="form-control @error('email') is-invalid @enderror" required>
                                    <label for="cEmail">Email *</label>
                                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="text" name="phone" id="cPhone" value="{{ old('phone') }}" placeholder="Phone" autocomplete="tel"
                                           class="form-control @error('phone') is-invalid @enderror">
                                    <label for="cPhone">Phone / WhatsApp</label>
                                    @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-floating">
                                    <select name="enquiry_type" id="cType" class="form-select @error('enquiry_type') is-invalid @enderror">
                                        <option value="">Select a topic</option>
                                        @foreach($enquiryTypes as $type)
                                            <option value="{{ $type }}" @selected(old('enquiry_type', $prefillType ?? null) === $type)>{{ $type }}</option>
                                        @endforeach
                                    </select>
                                    <label for="cType">What is your enquiry about?</label>
                                    @error('enquiry_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-floating">
                                    <textarea name="message" id="cMessage" placeholder="Message" style="height:160px;"
                                              class="form-control @error('message') is-invalid @enderror" required>{{ old('message', $prefillMessage ?? '') }}</textarea>
                                    <label for="cMessage">Your message *</label>
                                    @error('message')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                        </div>

                        <button type="submit" id="contactSubmit" class="btn btn-brand btn-lg mt-4 px-4">
                            <i class="bi bi-send me-2"></i>Send message
                        </button>
                    </form>
                </div>
            </div>

            <aside class="col-lg-5" data-reveal style="--d:.12s">
                <div class="contact-side-card contact-cta">
                    <div class="contact-side-title">Need prices fast?</div>
                    <p>Pick your products and send us a quotation request in a couple of minutes.</p>
                    <div class="d-grid gap-2">
                        <!-- <a href="{{ route('quote-request.create') }}" class="btn btn-brand"><i class="bi bi-receipt me-2"></i>Request a Quote</a> -->
                        @if($waLink)
                            <a href="{{ $waLink }}" target="_blank" rel="noopener" class="btn btn-brand "><i class="bi bi-whatsapp me-2"></i>Chat on WhatsApp</a>
                        @endif
                    </div>
                </div>

                <div class="contact-side-card">
                    <div class="contact-side-title">Details that help us quote faster</div>
                    <ul class="ct-ticks">
                        @foreach($tips as $tip)<li>{{ $tip }}</li>@endforeach
                    </ul>
                </div>

                @if(count($socials))
                    <div class="contact-side-card">
                        <div class="contact-side-title">Follow us</div>
                        <div class="contact-social">
                            @foreach($socials as [$icon, $label, $url])
                                <a href="{{ $url }}" target="_blank" rel="noopener" aria-label="{{ $label }}"><i class="bi {{ $icon }}"></i></a>
                            @endforeach
                        </div>
                    </div>
                @endif
            </aside>
        </div>
    </section>

    {{-- What happens next --}}
    <section class="ct-sec ct-mist">
        <div class="container">
            <div class="ct-head" data-reveal>
                <h2>What happens after you contact us</h2>
            </div>
            <ol class="ct-steps" data-reveal>
                @foreach($steps as [$title, $text])
                    <li style="--d: {{ $loop->index * .18 }}s"><strong>{{ $title }}</strong><span>{{ $text }}</span></li>
                @endforeach
            </ol>
        </div>
    </section>

    {{-- Find us --}}
    <section class="ct-sec">
        <div class="container">
            <div class="row g-5 align-items-center">
                <div class="col-lg-5" data-reveal>
                    <h2>Find {{ $company }} in Dubai</h2>
                    <p class="ct-lead">Visit our office or send your enquiry from anywhere in the UAE. We deliver across the Emirates.</p>
                    <dl class="ct-facts">
                        <div><dt>Company</dt><dd>{{ $company }}</dd></div>
                        <div><dt>Address</dt><dd>{{ $address }}</dd></div>
                        @if($phone)<div><dt>Phone</dt><dd><a href="{{ $telLink }}">{{ $phone }}</a></dd></div>@endif
                        @if($email)<div><dt>Email</dt><dd><a href="mailto:{{ $email }}">{{ $email }}</a></dd></div>@endif
                    </dl>
                    <a href="{{ $mapsUrl }}" target="_blank" rel="noopener" class="btn btn-outline-brand btn-lg">Open in Google Maps</a>
                </div>
                <div class="col-lg-7" data-reveal style="--d:.12s">
                    <div class="ct-map">
                        <iframe src="{{ $mapEmbed }}" title="Map showing the location of {{ $company }}"
                                loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen></iframe>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- FAQ --}}
    <section class="ct-sec ct-mist">
        <div class="container">
            <div class="row g-5">
                <div class="col-lg-4" data-reveal>
                    <h2>Questions before you get in touch</h2>
                    <p class="ct-lead">Cannot find your answer? Use the form above and we will reply.</p>
                </div>
                <div class="col-lg-8" data-reveal style="--d:.1s">
                    <div class="ct-faq">
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

</div>

<script type="application/ld+json">{!! json_encode($graph, $jsonFlags) !!}</script>
@endsection

@section('scripts')
<script>
    // Stop double submits and show that something is happening
    document.getElementById('contactForm').addEventListener('submit', function () {
        var btn = document.getElementById('contactSubmit');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Sending…';
    });

    // After sending (or a validation error), bring the form back into view
    (function () {
        var alertBox = document.querySelector('#contact-form .alert');
        if (alertBox) alertBox.scrollIntoView({ behavior: 'smooth', block: 'center' });
    })();

    // Reveal on scroll
    (function () {
        var els = document.querySelectorAll('.contact-page [data-reveal]');
        if (!document.documentElement.classList.contains('js') || !('IntersectionObserver' in window)) {
            els.forEach(function (el) { el.classList.add('in'); });
            return;
        }
        var io = new IntersectionObserver(function (entries) {
            entries.forEach(function (e) {
                if (e.isIntersecting) { e.target.classList.add('in'); io.unobserve(e.target); }
            });
        }, { threshold: 0.12, rootMargin: '0px 0px -6% 0px' });
        els.forEach(function (el) { io.observe(el); });
    })();
</script>
@endsection