@extends('frontend.layouts.app')
@section('title', $product->meta_title ?? $product->name.' | '.config('app.name'))
@section('meta_description', $product->meta_description ?? Str::limit(strip_tags($product->short_description), 150))

@section('content')
@php
    $faqs = $faqs ?? collect();

    $price   = $product->sale_price ?? $product->price;
    $onSale  = $product->price_type === 'sale' && $product->sale_price && $product->price > 0;
    $discount = $onSale ? (int) round((1 - $product->sale_price / $product->price) * 100) : null;

    [$stockLabel, $stockTone] = match ($product->stock_status) {
        'in_stock' => ['In stock', 'ok'],
        'preorder' => ['Pre-order', 'warn'],
        default    => ['Out of stock', 'bad'],
    };

    $waNumber = preg_replace('/\D+/', '', \App\Models\Setting::get('whatsapp_number', '971500000000'));
    $waLink   = 'https://wa.me/'.$waNumber.'?text='.urlencode($product->whatsappMessage());
    $phone    = \App\Models\Setting::get('phone');

    $hasOverview = filled($product->description);
    $hasSpecs    = $specGroups->isNotEmpty();
@endphp

<div class="container py-4 pd-page">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
            @if($product->category)
                <li class="breadcrumb-item"><a href="{{ route('categories.show', $product->category) }}">{{ $product->category->name }}</a></li>
            @endif
            <li class="breadcrumb-item active" aria-current="page">{{ $product->name }}</li>
        </ol>
    </nav>

    {{-- Hidden forms so buttons anywhere on the page can submit them --}}
    <form id="addForm" method="POST" action="{{ route('enquiry.add', $product) }}" class="d-none">@csrf</form>
    <form id="quickAddForm" method="POST" action="{{ route('enquiry.add', $product) }}" class="d-none">
        @csrf
        <input type="hidden" name="quantity" value="1" id="quickQty">
        <input type="hidden" name="redirect_to_quote" value="1">
    </form>

    <div class="row g-4 g-xl-5">
        {{-- ================= GALLERY ================= --}}
        <div class="col-lg-6">
            <div class="pd-gallery">
                @if($product->images->isNotEmpty())
                    <div id="productGallery" class="carousel slide pd-stage" data-bs-ride="false">
                        @if($discount)<span class="pd-badge">-{{ $discount }}%</span>@endif

                        <div class="carousel-inner">
                            @foreach($product->images as $image)
                                <div class="carousel-item {{ $loop->first ? 'active' : '' }}">
                                    <img src="{{ Storage::url($image->image) }}" class="pd-main-img"
                                         alt="{{ $image->alt_text ?: $product->name }}">
                                </div>
                            @endforeach
                        </div>

                        @if($product->images->count() > 1)
                            <button class="pd-arrow pd-arrow-prev" type="button" data-bs-target="#productGallery" data-bs-slide="prev" aria-label="Previous image">
                                <i class="bi bi-chevron-left"></i>
                            </button>
                            <button class="pd-arrow pd-arrow-next" type="button" data-bs-target="#productGallery" data-bs-slide="next" aria-label="Next image">
                                <i class="bi bi-chevron-right"></i>
                            </button>
                        @endif
                    </div>

                    @if($product->images->count() > 1)
                        <div class="pd-thumbs">
                            @foreach($product->images as $image)
                                <button type="button" class="pd-thumb {{ $loop->first ? 'active' : '' }}"
                                        data-bs-target="#productGallery" data-bs-slide-to="{{ $loop->index }}"
                                        aria-label="Show image {{ $loop->iteration }}">
                                    <img src="{{ Storage::url($image->image) }}" alt="">
                                </button>
                            @endforeach
                        </div>
                    @endif
                @else
                    <div class="pd-stage pd-stage-empty"><i class="bi bi-image"></i></div>
                @endif
            </div>
        </div>

        {{-- ================= INFO ================= --}}
        <div class="col-lg-6">
            <div class="d-flex align-items-center flex-wrap gap-2">
                @if($product->brand)
                    <a href="{{ route('brands.show', $product->brand) }}" class="pd-brand">{{ $product->brand->name }}</a>
                @endif
                <span class="pd-stock pd-stock-{{ $stockTone }}"><span class="dot"></span>{{ $stockLabel }}</span>
            </div>

            <h1 class="pd-title">{{ $product->name }}</h1>

            <div class="pd-meta">
                @if($product->sku)<span class="pd-chip">SKU <b>{{ $product->sku }}</b></span>@endif
                @if($product->model_number)<span class="pd-chip">Model <b>{{ $product->model_number }}</b></span>@endif
                @if($product->condition)<span class="pd-chip">Condition <b>{{ ucfirst($product->condition) }}</b></span>@endif
            </div>

            <div class="pd-price-box">
                @if($product->price_type === 'on_request' || $price === null)
                    <div class="pd-label mb-1">Price</div>
                    <div class="pd-price-request">Price on request</div>
                    <div class="small text-muted mt-1">Send a quote request and we will reply with the best price.</div>
                @else
                    <div class="pd-label mb-1">{{ $onSale ? 'Sale price' : 'Price' }}</div>
                    <div>
                        <span class="pd-price"><small>AED</small>{{ number_format($price, 2) }}</span>
                        @if($onSale)
                            <span class="pd-price-old">AED {{ number_format($product->price, 2) }}</span>
                            <span class="pd-save">Save {{ $discount }}%</span>
                        @endif
                    </div>
                @endif
            </div>

            @if($product->short_description)
                <p class="pd-lead">{{ $product->short_description }}</p>
            @endif

            <div class="pd-actions">
                <div class="pd-buy">
                    <div class="pd-qty">
                        <button type="button" data-qty="-1" aria-label="Decrease quantity"><i class="bi bi-dash"></i></button>
                        <input type="number" name="quantity" id="pdQty" value="1" min="1" form="addForm" aria-label="Quantity">
                        <button type="button" data-qty="1" aria-label="Increase quantity"><i class="bi bi-plus"></i></button>
                    </div>
                    <button type="submit" form="quickAddForm" class="btn btn-primary pd-quote">
                        <i class="bi bi-file-earmark-text me-2"></i>Request Quote
                    </button>
                </div>

                <div class="pd-secondary">
                    <a href="{{ $waLink }}" target="_blank" rel="noopener" class="btn btn-success">
                        <i class="bi bi-whatsapp me-2"></i>WhatsApp Enquiry
                    </a>
                    <button type="submit" form="addForm" class="btn btn-outline-primary">
                        <i class="bi bi-cart-plus me-2"></i>Add to Enquiry
                    </button>
                </div>
            </div>

            @if($product->variants->isNotEmpty())
                <div class="pd-label">Available configurations</div>
                <div class="pd-variants">
                    @foreach($product->variants as $variant)
                        <div class="pd-variant">
                            <span>{{ $variant->name }}</span>
                            <strong>
                                @if($variant->sale_price ?? $variant->price)
                                    AED {{ number_format($variant->sale_price ?? $variant->price, 2) }}
                                @else
                                    On request
                                @endif
                            </strong>
                        </div>
                    @endforeach
                </div>
            @endif

            @if($phone)
                <div class="pd-help">
                    Need help choosing? Call <a href="tel:{{ preg_replace('/[^\d+]/', '', $phone) }}">{{ $phone }}</a>
                </div>
            @endif
        </div>
    </div>

    {{-- ================= OVERVIEW / SPECIFICATIONS ================= --}}
    @if($hasOverview || $hasSpecs)
        <section class="pd-section">
            <div class="pd-card">
                <ul class="nav pd-tabs" role="tablist">
                    @if($hasOverview)
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" type="button" role="tab" data-bs-toggle="tab" data-bs-target="#pdOverview">Overview</button>
                        </li>
                    @endif
                    @if($hasSpecs)
                        <li class="nav-item" role="presentation">
                            <button class="nav-link {{ $hasOverview ? '' : 'active' }}" type="button" role="tab" data-bs-toggle="tab" data-bs-target="#pdSpecs">Specifications</button>
                        </li>
                    @endif
                </ul>

                <div class="tab-content">
                    @if($hasOverview)
                        <div class="tab-pane fade show active pd-pane" id="pdOverview" role="tabpanel">
                            <div class="pd-content">{!! nl2br(e($product->description)) !!}</div>
                        </div>
                    @endif

                    @if($hasSpecs)
                        <div class="tab-pane fade {{ $hasOverview ? '' : 'show active' }} pd-pane" id="pdSpecs" role="tabpanel">
                            @foreach($specGroups as $groupName => $specs)
                                <div class="pd-spec-group">
                                    <h6 class="pd-spec-title">{{ $groupName }}</h6>
                                    <dl class="pd-specs">
                                        @foreach($specs as $ps)
                                            <div class="pd-spec-row">
                                                <dt>{{ $ps->specification->name }}</dt>
                                                <dd>{{ $ps->value }}</dd>
                                            </div>
                                        @endforeach
                                    </dl>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </section>
    @endif

    {{-- ================= RELATED PRODUCTS ================= --}}
    @if($related->isNotEmpty())
        <section class="pd-section">
            <div class="pd-section-head">
                <div class="section-eyebrow">More to explore</div>
                <h2 class="section-title">Related products</h2>
            </div>
            <div class="row">
                @foreach($related as $relatedProduct)
                    @include('frontend.partials.product-card', ['product' => $relatedProduct])
                @endforeach
            </div>
        </section>
    @endif

    {{-- ================= FAQ ================= --}}
    @if($faqs->isNotEmpty())
        <section class="pd-section">
            <div class="row g-4 g-lg-5">
                <div class="col-lg-4">
                    <div class="pd-faq-aside">
                        <div class="section-eyebrow">FAQ</div>
                        <h2 class="section-title">Frequently asked questions</h2>
                        <p class="mt-2">Can't find what you are looking for? Send us a message and our team will get back to you.</p>
                        <a href="{{ route('contact.create') }}" class="btn btn-outline-brand">Contact us</a>
                    </div>
                </div>

                <div class="col-lg-8">
                    <div class="accordion faq-accordion" id="faqAccordion">
                        @foreach($faqs as $faq)
                            <div class="accordion-item">
                                <h3 class="accordion-header">
                                    <button class="accordion-button {{ $loop->first ? '' : 'collapsed' }}" type="button"
                                            data-bs-toggle="collapse" data-bs-target="#faq{{ $faq->id }}"
                                            aria-expanded="{{ $loop->first ? 'true' : 'false' }}" aria-controls="faq{{ $faq->id }}">
                                        {{ $faq->question }}
                                    </button>
                                </h3>
                                <div id="faq{{ $faq->id }}" class="accordion-collapse collapse {{ $loop->first ? 'show' : '' }}" data-bs-parent="#faqAccordion">
                                    <div class="accordion-body">{!! nl2br(e($faq->answer)) !!}</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <script type="application/ld+json">
                {!! json_encode([
                    '@context' => 'https://schema.org',
                    '@type' => 'FAQPage',
                    'mainEntity' => $faqs->map(fn ($f) => [
                        '@type' => 'Question',
                        'name' => $f->question,
                        'acceptedAnswer' => ['@type' => 'Answer', 'text' => $f->answer],
                    ])->values(),
                ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!}
            </script>
        </section>
    @endif
</div>

{{-- Mobile sticky action bar --}}
<div class="pd-mobile-bar d-md-none">
    <a href="{{ $waLink }}" target="_blank" rel="noopener" class="btn btn-success flex-fill">WhatsApp</a>
    <button type="submit" form="quickAddForm" class="btn btn-primary flex-fill">Request Quote</button>
</div>
@endsection

@section('scripts')
<script>
(function () {
    // Thumbnails follow the main image
    const gallery = document.getElementById('productGallery');
    if (gallery) {
        const thumbs = document.querySelectorAll('.pd-thumb');
        gallery.addEventListener('slid.bs.carousel', function (e) {
            thumbs.forEach(function (t, i) { t.classList.toggle('active', i === e.to); });
        });
    }

    // Quantity stepper; the chosen quantity is also sent with "Request Quote"
    const qty = document.getElementById('pdQty');
    const quickQty = document.getElementById('quickQty');
    if (qty) {
        const sync = function () {
            qty.value = Math.max(1, parseInt(qty.value || '1', 10) || 1);
            if (quickQty) quickQty.value = qty.value;
        };
        document.querySelectorAll('[data-qty]').forEach(function (b) {
            b.addEventListener('click', function () {
                qty.value = (parseInt(qty.value || '1', 10) || 1) + parseInt(b.dataset.qty, 10);
                sync();
            });
        });
        qty.addEventListener('input', sync);
        qty.addEventListener('change', sync);
    }
})();
</script>
@endsection