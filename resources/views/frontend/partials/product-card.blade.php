<div class="col-6 col-md-4 col-lg-3 mb-4">
    <div class="card h-100 border-0 shadow-sm" style="transition:.15s;" onmouseover="this.style.boxShadow='0 8px 20px rgba(228,0,43,.12)'" onmouseout="this.style.boxShadow=''">
        <a href="{{ route('products.show', $product) }}">
            @if($product->primaryImage)
                <img src="{{ Storage::url($product->primaryImage->image) }}" class="card-img-top" style="height:180px; object-fit:contain; padding:10px;" alt="{{ $product->name }}">
            @else
                <div class="bg-light d-flex align-items-center justify-content-center" style="height:180px;">
                    <i class="bi bi-image text-muted fs-1"></i>
                </div>
            @endif
        </a>
        <div class="card-body d-flex flex-column">
            @if($product->brand)
                <div class="text-muted small text-uppercase">{{ $product->brand->name }}</div>
            @endif
            <a href="{{ route('products.show', $product) }}" class="text-decoration-none" style="color:var(--ink);">
                <h6 class="card-title mb-1">{{ $product->name }}</h6>
            </a>
            <div class="mt-auto">
                @if($product->price_type === 'on_request')
                    <span class="text-muted small">Price on Request</span>
                @else
                    <span class="fw-bold" style="color:var(--brand-red);">AED {{ number_format($product->sale_price ?? $product->price, 2) }}</span>
                    @if($product->price_type === 'sale' && $product->sale_price)
                        <del class="text-muted small ms-1">AED {{ number_format($product->price, 2) }}</del>
                    @endif
                @endif
                <div class="small mt-1 {{ $product->stock_status === 'in_stock' ? 'text-success' : 'text-danger' }}">
                    {{ str_replace('_', ' ', ucfirst($product->stock_status)) }}
                </div>
                <div class="d-flex gap-2 mt-2">
                    <a href="{{ route('products.show', $product) }}" class="btn btn-outline-brand btn-sm flex-fill">View Details</a>
                    <a href="https://wa.me/{{ \App\Models\Setting::get('whatsapp_number', '971500000000') }}?text={{ urlencode($product->whatsappMessage()) }}"
                       target="_blank" class="btn btn-success btn-sm px-2" title="Enquire on WhatsApp" onclick="event.stopPropagation()">
                        <i class="bi bi-whatsapp"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>