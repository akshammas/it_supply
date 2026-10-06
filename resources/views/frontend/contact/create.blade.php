@extends('frontend.layouts.app')
@section('title', 'Contact Us | '.config('app.name'))

@section('content')
@php
    $company  = \App\Models\Setting::get('company_name', config('app.name'));
    $address  = \App\Models\Setting::get('address', 'UAE | Dubai');
    $email    = \App\Models\Setting::get('email');
    $phone    = \App\Models\Setting::get('phone');
    $waNumber = preg_replace('/\D/', '', (string) \App\Models\Setting::get('whatsapp_number'));
    $waLink   = $waNumber ? 'https://wa.me/'.$waNumber.'?text='.rawurlencode("Hello {$company}, I would like to enquire about your products.") : null;
    $telLink  = $phone ? 'tel:'.preg_replace('/[^\d+]/', '', $phone) : null;
    $mapsUrl  = \App\Models\Setting::get('google_maps_url') ?: 'https://www.google.com/maps/search/?api=1&query='.urlencode($address);
    $socials  = array_filter([
        ['bi-facebook',  'Facebook',  \App\Models\Setting::get('facebook_url')],
        ['bi-instagram', 'Instagram', \App\Models\Setting::get('instagram_url')],
        ['bi-linkedin',  'LinkedIn',  \App\Models\Setting::get('linkedin_url')],
    ], fn ($s) => !empty($s[2]));
@endphp

{{-- Header --}}
<section class="contact-hero">
    <div class="container">
        <div class="text-uppercase fw-bold small mb-2" style="letter-spacing:.1em; opacity:.8;">Contact Us</div>
        <h1 class="fw-bold display-5 mb-3" style="letter-spacing:-.02em;">Let's talk about your IT needs</h1>
        <p class="lead mb-0">Questions about products, pricing or a project? Send us a message, call, or chat with us on WhatsApp.</p>
    </div>
</section>

{{-- Contact cards --}}
<section class="container contact-cards">
    <div class="row g-3">
        <div class="col-sm-6 col-lg">
            <div class="contact-card">
                <div class="contact-icon"><i class="bi bi-geo-alt-fill"></i></div>
                <h6>Visit us</h6>
                <p>{{ $address }}</p>
                <a href="{{ $mapsUrl }}" target="_blank" rel="noopener" class="contact-link">Get directions <i class="bi bi-arrow-up-right"></i></a>
            </div>
        </div>

        @if($phone)
            <div class="col-sm-6 col-lg">
                <div class="contact-card">
                    <div class="contact-icon"><i class="bi bi-telephone-fill"></i></div>
                    <h6>Call us</h6>
                    <p>{{ $phone }}</p>
                    <a href="{{ $telLink }}" class="contact-link">Call now <i class="bi bi-arrow-up-right"></i></a>
                </div>
            </div>
        @endif

        @if($waLink)
            <div class="col-sm-6 col-lg">
                <div class="contact-card is-whatsapp">
                    <div class="contact-icon"><i class="bi bi-whatsapp"></i></div>
                    <h6>WhatsApp</h6>
                    <p>Chat with our team directly.</p>
                    <a href="{{ $waLink }}" target="_blank" rel="noopener" class="contact-link">Start chat <i class="bi bi-arrow-up-right"></i></a>
                </div>
            </div>
        @endif

        @if($email)
            <div class="col-sm-6 col-lg">
                <div class="contact-card">
                    <div class="contact-icon"><i class="bi bi-envelope-fill"></i></div>
                    <h6>Email us</h6>
                    <p>{{ $email }}</p>
                    <a href="mailto:{{ $email }}" class="contact-link">Send email <i class="bi bi-arrow-up-right"></i></a>
                </div>
            </div>
        @endif
    </div>
</section>

{{-- Form + sidebar --}}
<section class="container py-5">
    <div class="row g-4 g-lg-5">
        <div class="col-lg-7">
            <div class="contact-form-card" id="contact-form">
                <h3 class="fw-bold mb-1">Send us a message</h3>
                <p class="text-muted mb-4">Fill in the form and we'll get back to you shortly.</p>

                @if(session('status'))
                    <div class="alert alert-success d-flex align-items-center gap-2" role="alert">
                        <i class="bi bi-check-circle-fill fs-5"></i><div>{{ session('status') }}</div>
                    </div>
                @endif
                @if($errors->any())
                    <div class="alert alert-danger d-flex align-items-center gap-2" role="alert">
                        <i class="bi bi-exclamation-triangle-fill fs-5"></i><div>Please check the highlighted fields and try again.</div>
                    </div>
                @endif

                <form method="POST" action="{{ route('contact.store') }}" id="contactForm">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="text" name="name" id="cName" value="{{ old('name') }}" placeholder="Full name"
                                       class="form-control @error('name') is-invalid @enderror" required>
                                <label for="cName">Full name *</label>
                                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="text" name="company_name" id="cCompany" value="{{ old('company_name') }}" placeholder="Company"
                                       class="form-control @error('company_name') is-invalid @enderror">
                                <label for="cCompany">Company name</label>
                                @error('company_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="email" name="email" id="cEmail" value="{{ old('email') }}" placeholder="Email"
                                       class="form-control @error('email') is-invalid @enderror" required>
                                <label for="cEmail">Email *</label>
                                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="text" name="phone" id="cPhone" value="{{ old('phone') }}" placeholder="Phone"
                                       class="form-control @error('phone') is-invalid @enderror">
                                <label for="cPhone">Phone / WhatsApp</label>
                                @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-floating">
                                <select name="enquiry_type" id="cType" class="form-select @error('enquiry_type') is-invalid @enderror">
                                    <option value="">— Select —</option>
                                    @foreach($enquiryTypes as $type)
                                        <option value="{{ $type }}" @selected(old('enquiry_type') === $type)>{{ $type }}</option>
                                    @endforeach
                                </select>
                                <label for="cType">What is your enquiry about?</label>
                                @error('enquiry_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-floating">
                                <textarea name="message" id="cMessage" placeholder="Message" style="height:150px;"
                                          class="form-control @error('message') is-invalid @enderror" required>{{ old('message') }}</textarea>
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

        <aside class="col-lg-5">
            <div class="contact-side-card contact-cta">
                <div class="fw-bold fs-5 mb-1">Need prices fast?</div>
                <p>Pick your products and send us a quotation request in a couple of minutes.</p>
                <div class="d-grid gap-2">
                    <a href="{{ route('quote-request.create') }}" class="btn btn-brand"><i class="bi bi-receipt me-2"></i>Request a Quote</a>
                    @if($waLink)
                        <a href="{{ $waLink }}" target="_blank" rel="noopener" class="btn btn-success"><i class="bi bi-whatsapp me-2"></i>Chat on WhatsApp</a>
                    @endif
                </div>
            </div>

            <div class="contact-side-card">
                <div class="contact-side-title">Find us</div>
                <div class="d-flex gap-3">
                    <div class="contact-icon mb-0 flex-shrink-0"><i class="bi bi-geo-alt-fill"></i></div>
                    <div>
                        <div class="fw-bold">{{ $company }}</div>
                        <div class="text-muted small mb-2">{{ $address }}</div>
                        <a href="{{ $mapsUrl }}" target="_blank" rel="noopener" class="contact-link">Open in Google Maps <i class="bi bi-arrow-up-right"></i></a>
                    </div>
                </div>
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
@endsection

@section('scripts')
<script>
    // Stop double submits and show that something is happening
    document.getElementById('contactForm').addEventListener('submit', function () {
        var btn = document.getElementById('contactSubmit');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Sending…';
    });
</script>
@endsection