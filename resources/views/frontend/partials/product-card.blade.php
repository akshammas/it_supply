@php
    $price    = $product->sale_price ?? $product->price;
    $onSale   = $product->price_type === 'sale' && $product->sale_price && $product->price > 0;
    $discount = $onSale ? (int) round((1 - $product->sale_price / $product->price) * 100) : null;

    [$stockLabel, $stockTone] = match ($product->stock_status) {
        'in_stock' => ['In stock', 'ok'],
        'preorder' => ['Pre-order', 'warn'],
        default    => ['Out of stock', 'bad'],
    };

    $waNumber = preg_replace('/\D+/', '', \App\Models\Setting::get('whatsapp_number', '971500000000'));
    $waLink   = 'https://wa.me/'.$waNumber.'?text='.urlencode($product->whatsappMessage());
@endphp

<div class="col-6 col-md-4 col-lg-3 mb-4">
    <article class="pc">
        <div class="pc-media">
            @if($discount)<span class="pc-badge">-{{ $discount }}%</span>@endif

            @if($product->primaryImage)
                <img src="{{ Storage::url($product->primaryImage->image) }}" alt="{{ $product->name }}" loading="lazy">
            @else
                <div class="pc-empty"><i class="bi bi-image"></i></div>
            @endif
        </div>

        <div class="pc-body">
            @if($product->brand)
                <div class="pc-brand">{{ $product->brand->name }}</div>
            @endif

            {{-- stretched-link makes the whole card clickable --}}
            <a href="{{ route('products.show', $product) }}" class="pc-title stretched-link">{{ $product->name }}</a>

            <div class="pc-foot">
                <div class="pc-price-row">
                    @if($product->price_type === 'on_request' || $price === null)
                        <span class="pc-price-request">Price on request</span>
                    @else
                        <span class="pc-price">AED {{ number_format($price, 2) }}</span>
                        @if($onSale)<span class="pc-price-old">AED {{ number_format($product->price, 2) }}</span>@endif
                    @endif
                </div>

                <div class="pc-stock pc-stock-{{ $stockTone }}"><span class="dot"></span>{{ $stockLabel }}</div>

                <div class="pc-actions">
                    <a href="{{ route('products.show', $product) }}" class="btn btn-outline-brand btn-sm flex-fill">View details</a>
                    <a href="{{ $waLink }}" target="_blank" rel="noopener" class="btn btn-success btn-sm"
                       title="Enquire on WhatsApp" aria-label="Enquire on WhatsApp">
                        <i class="bi bi-whatsapp"></i>
                    </a>
                </div>
            </div>
        </div>
    </article>
</div>