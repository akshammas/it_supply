@php
    // Everything here comes from Admin > Settings; empty values are simply not shown
    $fCompany = \App\Models\Setting::get('company_name', config('app.name'));
    $fEmail   = \App\Models\Setting::get('email');
    $fPhone   = \App\Models\Setting::get('phone');
    $fTel     = $fPhone ? 'tel:' . preg_replace('/[^\d+]/', '', $fPhone) : null;
    $fAddress = \App\Models\Setting::get('address', 'UAE | Dubai');
    $fMap     = \App\Models\Setting::get('google_maps_url');

    $fWaNumber = preg_replace('/\D+/', '', (string) \App\Models\Setting::get('whatsapp_number', ''));
    $fWaLink   = $fWaNumber ? 'https://wa.me/' . $fWaNumber . '?text=' . rawurlencode('Hello, I would like to enquire about your products.') : null;

    // [icon, label, url]
    $fSocial = array_filter([
        ['bi-facebook',  'Facebook',  \App\Models\Setting::get('facebook_url')],
        ['bi-instagram', 'Instagram', \App\Models\Setting::get('instagram_url')],
        ['bi-linkedin',  'LinkedIn',  \App\Models\Setting::get('linkedin_url')],
        ['bi-whatsapp',  'WhatsApp',  $fWaLink],
    ], fn ($s) => ! empty($s[2]));

    // Top categories and brands, cached for an hour (same idea as the mega menu)
    $fLinks = \Illuminate\Support\Facades\Cache::remember('footer.links', now()->addHour(), function () {
        return [
            'categories' => \App\Models\Category::whereNull('parent_id')->where('status', true)
                ->orderBy('sort_order')->take(6)->get(['name', 'slug'])->map(fn ($c) => $c->only(['name', 'slug']))->all(),
            'brands' => \App\Models\Brand::where('status', true)
                ->orderBy('sort_order')->take(6)->get(['name', 'slug'])->map(fn ($b) => $b->only(['name', 'slug']))->all(),
        ];
    });

    // Useful links: fixed links first, then your custom pages. Never more than 6.
    $fUseful = [
        ['About us', route('about')],
        ['Blog', route('blog.index')],
        ['Contact', route('contact.create')],
        ['Enquiry list', route('enquiry.index')],
    ];
    foreach (\App\Models\Page::where('status', true)->orderBy('title')->take(6)->get() as $fPage) {
        $fUseful[] = [$fPage->title, route('pages.show', $fPage)];
    }
    $fUseful = array_slice($fUseful, 0, 6);
@endphp
<footer class="site-footer mt-5 hm-grid" role="contentinfo">

    {{-- Call to action --}}
    <div class="container">
        <div class="sf-cta">
            <div>
                <h2 class="sf-cta-title">Looking for IT hardware for your business?</h2>
                <p class="sf-cta-text">Tell us what you need and our team will reply with a quotation.</p>
            </div>
            <div class="sf-cta-actions">
                <a href="{{ route('contact.create', ['type' => 'quote']) }}#contact-form" class="btn btn-brand btn-lg">Request a quote</a>
                @if($fWaLink)
                    <a href="{{ $fWaLink }}" class="btn btn-wa btn-lg" target="_blank" rel="noopener"><i class="bi bi-whatsapp"></i>WhatsApp us</a>
                @endif
            </div>
        </div>
    </div>

    {{-- Trust points (static text: edit to suit) --}}
    <div class="container">
        <ul class="sf-trust">
            <li><i class="bi bi-patch-check"></i><div><strong>Genuine products</strong><span>Original, brand-new IT hardware</span></div></li>
            <li><i class="bi bi-receipt"></i><div><strong>Business quotations</strong><span>Bulk and corporate pricing on request</span></div></li>
            <li><i class="bi bi-truck"></i><div><strong>Delivery across the UAE</strong><span>Dispatched from Dubai</span></div></li>
            <li><i class="bi bi-headset"></i><div><strong>Expert support</strong><span>Pre-sales advice from our team</span></div></li>
        </ul>
    </div>

    {{-- Main columns --}}
    <div class="container sf-main">
        <div class="row g-4 g-xl-5">
            <div class="col-lg-4">
                <a href="{{ route('home') }}" class="sf-brand">{{ $fCompany }}<span>.</span></a>
                <p class="sf-about">Enterprise IT products and solutions for businesses across the UAE. Laptops, networking, servers and accessories from trusted brands.</p>

                @if($fSocial)
                    <ul class="sf-social" aria-label="Follow us">
                        @foreach($fSocial as [$icon, $label, $url])
                            <li><a href="{{ $url }}" target="_blank" rel="noopener" aria-label="{{ $label }}" title="{{ $label }}"><i class="bi {{ $icon }}"></i></a></li>
                        @endforeach
                    </ul>
                @endif
            </div>

            <div class="col-6 col-md-4 col-lg-2">
                <h6 class="sf-title">Products</h6>
                <ul class="sf-links">
                    @foreach($fLinks['categories'] as $fc)
                        <li><a href="{{ route('categories.show', $fc['slug']) }}">{{ $fc['name'] }}</a></li>
                    @endforeach
                    <li><a href="{{ route('products.index') }}" class="sf-more">All products <i class="bi bi-arrow-right"></i></a></li>
                </ul>
            </div>

            <div class="col-6 col-md-4 col-lg-2">
                <h6 class="sf-title">Useful links</h6>
                <ul class="sf-links">
                    @foreach($fUseful as [$fLabel, $fUrl])
                        <li><a href="{{ $fUrl }}">{{ $fLabel }}</a></li>
                    @endforeach
                </ul>
            </div>

            <div class="col-md-4 col-lg-4">
                <h6 class="sf-title">Get in touch</h6>
                <ul class="sf-contact">
                    @if($fAddress)
                        <li>
                            <i class="bi bi-geo-alt"></i>
                            @if($fMap)<a href="{{ $fMap }}" target="_blank" rel="noopener">{{ $fAddress }}</a>@else<span>{{ $fAddress }}</span>@endif
                        </li>
                    @endif
                    @if($fEmail)<li><i class="bi bi-envelope"></i><a href="mailto:{{ $fEmail }}">{{ $fEmail }}</a></li>@endif
                    @if($fPhone)<li><i class="bi bi-telephone"></i><a href="{{ $fTel }}">{{ $fPhone }}</a></li>@endif
                    @if($fWaLink)<li><i class="bi bi-whatsapp"></i><a href="{{ $fWaLink }}" target="_blank" rel="noopener">Chat on WhatsApp</a></li>@endif
                    {{-- Static opening hours: edit to suit --}}
                    <li><i class="bi bi-clock"></i><span>Sat - Thu: 9:00 AM - 6:00 PM</span></li>
                </ul>
            </div>
        </div>

        @if(count($fLinks['brands']))
            <div class="sf-brands">
                <span class="sf-brands-label">Brands we supply</span>
                <ul>
                    @foreach($fLinks['brands'] as $fb)
                        <li><a href="{{ route('brands.show', $fb['slug']) }}">{{ $fb['name'] }}</a></li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>

    {{-- Bottom bar --}}
    <div class="sf-bottom">
        <div class="container sf-bottom-in">
            <p>&copy; {{ date('Y') }} {{ $fCompany }}. All rights reserved.</p>
            <a href="#" class="sf-top" aria-label="Back to top"><i class="bi bi-arrow-up"></i>Back to top</a>
        </div>
    </div>
</footer>