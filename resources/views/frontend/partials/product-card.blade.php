<div class="col-6 col-md-4 col-lg-3 mb-4">
    <div class="card h-100 shadow-sm">
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
            <a href="{{ route('products.show', $product) }}" class="text-decoration-none text-dark">
                <h6 class="card-title mb-1">{{ $product->name }}</h6>
            </a>
            <div class="mt-auto">
                @if($product->price_type === 'on_request')
                    <span class="text-muted small">Price on Request</span>
                @else
                    <span class="fw-semibold">AED {{ number_format($product->sale_price ?? $product->price, 2) }}</span>
                    @if($product->price_type === 'sale' && $product->sale_price)
                        <del class="text-muted small ms-1">AED {{ number_format($product->price, 2) }}</del>
                    @endif
                @endif
                <div class="small mt-1 {{ $product->stock_status === 'in_stock' ? 'text-success' : 'text-danger' }}">
                    {{ str_replace('_', ' ', ucfirst($product->stock_status)) }}
                </div>
            </div>
        </div>
    </div>
</div>
