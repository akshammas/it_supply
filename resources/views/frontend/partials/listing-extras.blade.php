{{--
    Content shown BELOW the product cards on category and brand pages:
      1. admin-written text or HTML ($seoContent, converted by App\Support\PageContent)
      2. blog cards                 ($blogs)
      3. FAQ accordion              ($faqs)
    Each part is skipped when it has nothing to show.
    Expects: $name, $seoContent, $blogs, $faqs
--}}
@php
    $seoHtml = \App\Support\PageContent::render($seoContent ?? '');
    $blogs = $blogs ?? collect();
    $faqs = $faqs ?? collect();
@endphp

@if($seoHtml !== '')
    <section class="listing-seo mt-5">
        {{-- Admin-written; plain text is converted safely, real HTML is trusted (admin only). --}}
        {!! $seoHtml !!}
    </section>
@endif

@if($blogs->isNotEmpty())
    <section class="listing-blogs mt-5">
        <div class="d-flex flex-wrap align-items-end justify-content-between gap-2 mb-4">
            <div>
                <div class="section-eyebrow">Blog</div>
                <h2 class="section-title mb-0">Latest articles on {{ $name }}</h2>
            </div>
            <a href="{{ route('blog.index') }}" class="btn btn-outline-brand btn-sm">View all articles</a>
        </div>
        <div class="row g-4">
            @foreach($blogs as $post)
                <div class="col-md-6 col-lg-4">
                    @include('frontend.partials.blog-card', ['post' => $post])
                </div>
            @endforeach
        </div>
    </section>
@endif

@if($faqs->isNotEmpty())
    <section class="listing-faq mt-5">
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
                <div class="accordion faq-accordion" id="listingFaqAccordion">
                    @foreach($faqs as $faq)
                        <div class="accordion-item">
                            <h3 class="accordion-header">
                                <button class="accordion-button {{ $loop->first ? '' : 'collapsed' }}" type="button"
                                        data-bs-toggle="collapse" data-bs-target="#listingFaq{{ $faq->id }}"
                                        aria-expanded="{{ $loop->first ? 'true' : 'false' }}" aria-controls="listingFaq{{ $faq->id }}">
                                    {{ $faq->question }}
                                </button>
                            </h3>
                            <div id="listingFaq{{ $faq->id }}" class="accordion-collapse collapse {{ $loop->first ? 'show' : '' }}" data-bs-parent="#listingFaqAccordion">
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
