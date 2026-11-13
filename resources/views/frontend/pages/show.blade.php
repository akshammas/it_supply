@php
    $company = \App\Models\Setting::get('company_name', config('app.name'));
    $email   = \App\Models\Setting::get('email');
    $phone   = \App\Models\Setting::get('phone');
    $telLink = $phone ? 'tel:' . preg_replace('/[^\d+]/', '', $phone) : null;

    // WhatsApp number comes from Admin > Settings (whatsapp_number); hidden when empty
    $waNumber = preg_replace('/\D+/', '', (string) \App\Models\Setting::get('whatsapp_number', ''));
    $waLink   = $waNumber
        ? 'https://wa.me/' . $waNumber . '?text=' . rawurlencode('Hello, I have a question about "' . $page->title . '"')
        : null;

    $content = (string) $page->content;
    $isHtml  = $content !== strip_tags($content);
    $safe    = $isHtml ? \App\Support\SafeHtml::clean($content) : '';

    // Plain text of the page (scripts and styles are already removed), used for the meta description
    $plain = $isHtml ? html_entity_decode(strip_tags($safe), ENT_QUOTES | ENT_HTML5, 'UTF-8') : $content;

    $pageTitle = $page->meta_title ?: $page->title . ' | ' . config('app.name');
    $pageDesc  = $page->meta_description
        ?: \Illuminate\Support\Str::limit(trim(preg_replace('/\s+/', ' ', $plain)), 155);
    $pageUrl   = url()->current();
    $coverUrl  = $page->featured_image ? url(Storage::url($page->featured_image)) : null;

    // Unique, URL-safe ids for headings (used by the table of contents)
    $toc  = [];
    $used = [];
    $makeId = function (string $text) use (&$used) {
        $id = \Illuminate\Support\Str::slug($text) ?: 'section';
        $used[$id] = ($used[$id] ?? 0) + 1;
        return $used[$id] > 1 ? $id . '-' . $used[$id] : $id;
    };

    if ($isHtml) {
        // Content written with HTML: SafeHtml keeps basic formatting and removes scripts,
        // event handlers (onerror=...), javascript: links, iframes, forms and styles.
        $body = $safe;
        $body = preg_replace('/<(\/?)h1\b/i', '<$1h2', $body);   // one h1 per page: the title
        $body = preg_replace_callback('/<h([23])([^>]*)>(.*?)<\/h\1>/is', function ($m) use (&$toc, $makeId) {
            $text = trim(strip_tags($m[3]));
            if ($text === '') return $m[0];
            $id = $makeId($text);
            $toc[] = ['level' => (int) $m[1], 'id' => $id, 'text' => $text];
            $attrs = preg_replace('/\sid=("|\').*?\1/i', '', $m[2]);
            return '<h' . $m[1] . $attrs . ' id="' . $id . '">' . $m[3] . '</h' . $m[1] . '>';
        }, $body);
    } else {
        // Plain text typed in the admin box. Everything is escaped, so it is safe.
        // Blank line = new paragraph. "# Title" or "## Title" = heading, "### Title" = sub-heading.
        // Lines starting with "- " make a bullet list, lines starting with "1. " a numbered list.
        $inline = function (string $s) {
            $s = e($s);
            $s = preg_replace('~(https?://[^\s<]+[^\s<.,;:!?)])~i', '<a href="$1" rel="noopener">$1</a>', $s);
            $s = preg_replace('~([A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,})~i', '<a href="mailto:$1">$1</a>', $s);
            return $s;
        };
        $body = '';
        $para = [];
        $list = [];
        $listType = null;
        $flush = function () use (&$body, &$para, &$list, &$listType, $inline) {
            if ($para) {
                $body .= '<p>' . implode('<br>', array_map($inline, $para)) . '</p>';
                $para = [];
            }
            if ($list) {
                $tag = $listType === 'ol' ? 'ol' : 'ul';
                $body .= '<' . $tag . '>' . implode('', array_map(fn ($li) => '<li>' . $inline($li) . '</li>', $list)) . '</' . $tag . '>';
                $list = [];
                $listType = null;
            }
        };
        foreach (preg_split('/\R/', $content) as $line) {
            $t = trim($line);
            if ($t === '') { $flush(); continue; }
            if (preg_match('/^(#{1,3})\s+(.+)$/', $t, $m)) {
                $flush();
                $level = strlen($m[1]) === 3 ? 3 : 2;
                $id = $makeId($m[2]);
                $toc[] = ['level' => $level, 'id' => $id, 'text' => $m[2]];
                $body .= '<h' . $level . ' id="' . $id . '">' . e($m[2]) . '</h' . $level . '>';
                continue;
            }
            if (preg_match('/^[-*•]\s+(.+)$/u', $t, $m)) {
                if ($listType !== 'ul') $flush();
                $listType = 'ul'; $list[] = $m[1];
                continue;
            }
            if (preg_match('/^\d+[.)]\s+(.+)$/', $t, $m)) {
                if ($listType !== 'ol') $flush();
                $listType = 'ol'; $list[] = $m[1];
                continue;
            }
            if ($list) $flush();
            $para[] = $t;
        }
        $flush();
    }

    // Other published pages, same query as the footer
    $others  = \App\Models\Page::where('status', true)->orderBy('title')->get();
    $showToc = count($toc) >= 3;
    $hasSide = $others->count() > 1 || $showToc;

    $graph = [
        '@context' => 'https://schema.org',
        '@graph' => [
            array_filter([
                '@type' => 'WebPage',
                '@id'   => $pageUrl . '#webpage',
                'url'   => $pageUrl,
                'name'  => $page->title,
                'description' => $pageDesc,
                'image' => $coverUrl,
                'dateModified' => optional($page->updated_at)->toIso8601String(),
                'isPartOf' => ['@type' => 'WebSite', 'name' => $company, 'url' => url('/')],
                'breadcrumb' => ['@id' => $pageUrl . '#breadcrumb'],
            ]),
            [
                '@type' => 'BreadcrumbList',
                '@id'   => $pageUrl . '#breadcrumb',
                'itemListElement' => [
                    ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => url('/')],
                    ['@type' => 'ListItem', 'position' => 2, 'name' => $page->title, 'item' => $pageUrl],
                ],
            ],
        ],
    ];
    $jsonFlags = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG;
@endphp
@extends('frontend.layouts.app')
@section('title', $pageTitle)
@section('meta_description', $pageDesc)
@if($coverUrl)
    @section('og_image', $coverUrl)
@endif

@section('content')
<div class="pg-page">

    {{-- Header --}}
    <section class="pg-hero">
        <div class="container pg-in">
            <nav class="pg-crumbs" aria-label="Breadcrumb" style="--i:0"><a href="{{ route('home') }}">Home</a> / {{ \Illuminate\Support\Str::limit($page->title, 60) }}</nav>
            <h1 style="--i:1">{{ $page->title }}</h1>
            @if($page->meta_description)<p class="pg-lede" style="--i:2">{{ $page->meta_description }}</p>@endif
        </div>
    </section>

    <section class="container pg-body">
        <div class="row g-4 g-xl-5">
            @if($hasSide)
                <aside class="col-lg-4 col-xl-3 pg-side-col">
                    <div class="pg-side">
                        @if($others->count() > 1)
                            <nav class="pg-card" aria-label="Information pages">
                                <div class="pg-card-title">Information</div>
                                <ul class="pg-nav">
                                    @foreach($others as $other)
                                        <li>
                                            <a href="{{ route('pages.show', $other) }}" @if($other->id === $page->id) class="active" aria-current="page" @endif>{{ $other->title }}</a>
                                        </li>
                                    @endforeach
                                </ul>
                            </nav>
                        @endif

                        @if($showToc)
                            <nav class="pg-card" id="pgToc" aria-label="On this page">
                                <div class="pg-card-title">On this page</div>
                                <ul class="pg-toc">
                                    @foreach($toc as $t)
                                        <li><a href="#{{ $t['id'] }}" @if($t['level'] === 3) class="sub" @endif>{{ $t['text'] }}</a></li>
                                    @endforeach
                                </ul>
                            </nav>
                        @endif
                    </div>
                </aside>
            @endif

            <div class="{{ $hasSide ? 'col-lg-8 col-xl-9' : 'col-lg-10 col-xl-8 mx-auto' }}">
                @if($page->featured_image)
                    <figure class="pg-cover">
                        <img src="{{ Storage::url($page->featured_image) }}" alt="{{ $page->title }}" fetchpriority="high" decoding="async">
                    </figure>
                @endif

                <article class="pg-content">{!! $body !!}</article>

                <div class="pg-help">
                    <div>
                        <h2>Questions about this page?</h2>
                        <p>Our team is happy to help.@if($email || $phone) You can also reach us directly:@endif</p>
                        @if($email || $phone)
                            <p class="pg-help-links">
                                @if($email)<a href="mailto:{{ $email }}"><i class="bi bi-envelope"></i>{{ $email }}</a>@endif
                                @if($phone)<a href="{{ $telLink }}"><i class="bi bi-telephone"></i>{{ $phone }}</a>@endif
                            </p>
                        @endif
                    </div>
                    <div class="pg-help-actions">
                        @if($waLink)
                            <a href="{{ $waLink }}" class="btn btn-wa btn-lg" target="_blank" rel="noopener">
                                <i class="bi bi-whatsapp"></i>WhatsApp us
                            </a>
                        @endif
                        <a href="{{ route('contact.create') }}" class="btn btn-brand btn-lg">Contact us</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

</div>

<script type="application/ld+json">{!! json_encode($graph, $jsonFlags) !!}</script>
@endsection

@section('scripts')
@if($showToc)
<script>
    // Highlight the current section in "On this page"
    (function () {
        if (!('IntersectionObserver' in window)) return;
        var links = {};
        document.querySelectorAll('#pgToc a').forEach(function (a) { links[a.getAttribute('href').slice(1)] = a; });
        var heads = document.querySelectorAll('.pg-content h2[id], .pg-content h3[id]');
        var spy = new IntersectionObserver(function (entries) {
            entries.forEach(function (e) {
                if (!e.isIntersecting) return;
                Object.keys(links).forEach(function (k) { links[k].classList.toggle('active', k === e.target.id); });
            });
        }, { rootMargin: '-15% 0px -75% 0px' });
        heads.forEach(function (h) { spy.observe(h); });
    })();
</script>
@endif
@endsection