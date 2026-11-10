@extends('frontend.layouts.app')
@section('title', 'Your Enquiry List | '.config('app.name'))
{{-- A cart-style page should not appear in search results --}}
@section('robots', 'noindex, follow')

@php
    $waNumber = preg_replace('/\D+/', '', (string) \App\Models\Setting::get('whatsapp_number', ''));
    $eqCount  = $items->count();
    $eqUnits  = (int) $items->sum('quantity');
@endphp

@section('content')
<div class="eq-page">

    {{-- Header --}}
    <section class="eq-hero">
        <div class="container eq-in">
            <nav class="eq-crumbs" aria-label="Breadcrumb" style="--i:0"><a href="{{ route('home') }}">Home</a> / Enquiry list</nav>
            <h1 style="--i:1">Your enquiry list</h1>
            <p class="eq-lede" style="--i:2">
                @if($eqCount)
                    {{ $eqCount }} {{ \Illuminate\Support\Str::plural('product', $eqCount) }}, {{ $eqUnits }} {{ \Illuminate\Support\Str::plural('unit', $eqUnits) }} in total.
                    Check the quantities, then send the list to our team.
                @else
                    Add products to build a list, then send it to our team for a quotation.
                @endif
            </p>
        </div>
    </section>

    <section class="container eq-body">
        @if(session('status'))
            <div class="alert alert-success eq-alert d-flex align-items-center gap-2" role="alert">
                <i class="bi bi-check-circle fs-5"></i><div>{{ session('status') }}</div>
            </div>
        @endif

        @if($items->isEmpty())
            <div class="eq-empty">
                <span class="eq-empty-icon"><i class="bi bi-cart"></i></span>
                <h2>Your enquiry list is empty</h2>
                <p>Browse the catalogue and add the products you want a quotation for.</p>
                <a href="{{ route('products.index') }}" class="btn btn-brand btn-lg me-2 mb-2">Browse Products</a>
                <a href="{{ route('contact.create') }}" class="btn btn-outline-brand btn-lg mb-2">Contact us</a>
            </div>
        @else
            <div class="row g-4 g-xl-5">
                {{-- Items --}}
                <div class="col-lg-8">
                    <ul class="eq-list">
                        @foreach($items as $item)
                            @php $eqProduct = $item['product']; @endphp
                            <li class="eq-item" style="--i: {{ $loop->index }}">
                                <a href="{{ route('products.show', $eqProduct) }}" class="eq-thumb" tabindex="-1" aria-hidden="true">
                                    @if($eqProduct->primaryImage)
                                        <img src="{{ Storage::url($eqProduct->primaryImage->image) }}" alt="" loading="lazy" decoding="async">
                                    @else
                                        <i class="bi bi-box-seam"></i>
                                    @endif
                                </a>

                                <div class="eq-info">
                                    <a href="{{ route('products.show', $eqProduct) }}" class="eq-name">{{ $eqProduct->name }}</a>
                                    @if($eqProduct->sku)<div class="eq-sku">SKU: {{ $eqProduct->sku }}</div>@endif
                                </div>

                                <form method="POST" action="{{ route('enquiry.update', $eqProduct) }}" class="eq-qty-form" data-qty-form>
                                    @csrf
                                    <div class="eq-stepper">
                                        <button type="button" data-step="-1" aria-label="Decrease quantity">&minus;</button>
                                        <input type="number" name="quantity" value="{{ $item['quantity'] }}" min="1" inputmode="numeric"
                                               aria-label="Quantity for {{ $eqProduct->name }}">
                                        <button type="button" data-step="1" aria-label="Increase quantity">+</button>
                                    </div>
                                    <button type="submit" class="eq-update">Update</button>
                                </form>

                                <form method="POST" action="{{ route('enquiry.remove', $eqProduct) }}" class="eq-remove"
                                      onsubmit="return confirm('Remove this item?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" aria-label="Remove {{ $eqProduct->name }}"><i class="bi bi-trash"></i><span>Remove</span></button>
                                </form>
                            </li>
                        @endforeach
                    </ul>

                    <a href="{{ route('products.index') }}" class="eq-more">Continue browsing</a>
                </div>

                {{-- Summary --}}
                <aside class="col-lg-4">
                    <div class="eq-summary">
                        <h2>Enquiry summary</h2>
                        <dl class="eq-totals">
                            <div><dt>Products</dt><dd>{{ $eqCount }}</dd></div>
                            <div><dt>Total units</dt><dd>{{ $eqUnits }}</dd></div>
                        </dl>
                        <p>Send this list and our team replies with pricing and availability.</p>

                        <div class="d-grid gap-2">
                            <a href="{{ route('quote-request.create') }}" class="btn btn-brand btn-lg">
                                <i class="bi bi-file-earmark-text me-1"></i> Request Quote
                            </a>
                            @if($waNumber)
                                <a href="https://wa.me/{{ $waNumber }}?text={{ urlencode($whatsappMessage) }}"
                                   target="_blank" rel="noopener" class="btn btn-outline-light btn-lg">
                                    <i class="bi bi-whatsapp me-1"></i> WhatsApp
                                </a>
                            @endif
                        </div>

                        <ol class="eq-next">
                            <li>We review your list</li>
                            <li>We reply with pricing and availability</li>
                            <li>You confirm and we deliver across the UAE</li>
                        </ol>
                    </div>
                </aside>
            </div>
        @endif
    </section>

</div>
@endsection

@section('scripts')
<script>
    // Quantity steppers: the Update button is only active once the number has changed.
    // Without JavaScript the plain number field and Update button still work.
    document.querySelectorAll('[data-qty-form]').forEach(function (form) {
        var input = form.querySelector('input[name="quantity"]');
        var btn = form.querySelector('.eq-update');
        var initial = String(parseInt(input.value, 10));

        function sync() {
            var v = parseInt(input.value, 10);
            btn.disabled = !(v >= 1) || String(v) === initial;
        }

        form.querySelectorAll('[data-step]').forEach(function (b) {
            b.addEventListener('click', function () {
                var v = parseInt(input.value, 10) || 1;
                input.value = Math.max(1, v + parseInt(b.getAttribute('data-step'), 10));
                sync();
            });
        });
        input.addEventListener('input', sync);

        form.addEventListener('submit', function () {
            btn.disabled = true;
            btn.textContent = 'Updating…';
        });
        sync();
    });
</script>
@endsection