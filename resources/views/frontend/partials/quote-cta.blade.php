{{-- "Need a quotation?" band shown at the very bottom of category and brand pages.
     Address / email / phone come from Admin > Settings. --}}
@php
    $ctaAddress = \App\Models\Setting::get('address', 'UAE | Dubai');
    $ctaEmail   = \App\Models\Setting::get('email', 'info@example.com');
    $ctaPhone   = \App\Models\Setting::get('phone', '+971 00 000 0000');
@endphp
<section class="about-section quote-cta">
    <div class="container">
        <div class="row g-5 align-items-center">
            <div class="col-lg-6">
                <h2>Need a quotation?</h2>
                <p class="mb-4">
                    Add products to your quote list or send us your requirements, and our team will reply.
                </p>
                <a href="{{ route('products.index') }}" class="btn btn-brand btn-lg me-2 mb-2">Browse products</a>
                <a href="{{ route('contact.create') }}" class="btn btn-outline-brand btn-lg mb-2">Contact us</a>
            </div>
            <div class="col-lg-6">
                <ul class="contact-list">
                    @if(filled($ctaAddress))
                        <li><i class="bi bi-geo-alt"></i><span>{{ $ctaAddress }}</span></li>
                    @endif
                    @if(filled($ctaEmail))
                        <li><i class="bi bi-envelope"></i><a href="mailto:{{ $ctaEmail }}">{{ $ctaEmail }}</a></li>
                    @endif
                    @if(filled($ctaPhone))
                        <li><i class="bi bi-telephone"></i><a href="tel:{{ preg_replace('/[^\d+]/', '', $ctaPhone) }}">{{ $ctaPhone }}</a></li>
                    @endif
                </ul>
            </div>
        </div>
    </div>
</section>
