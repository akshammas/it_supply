@php
    $company = \App\Models\Setting::get('company_name', config('app.name'));
    $email   = \App\Models\Setting::get('email');
    $phone   = \App\Models\Setting::get('phone');
    $telLink = $phone ? 'tel:' . preg_replace('/[^\d+]/', '', $phone) : null;

    $content = (string) $page->content;
    $isHtml  = $content !== strip_tags($content);
    $minutes = max(1, (int) ceil(str_word_count(strip_tags($content)) / 200));

    $pageTitle = $page->meta_title ?: $page->title . ' | ' . config('app.name');
    $pageDesc  = $page->meta_description
        ?: \Illuminate\Support\Str::limit(trim(preg_replace('/\s+/', ' ', strip_tags($content))), 155);
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
        // Content written with HTML: keep basic formatting, drop scripts, styles, iframes and similar
        $allowed = '<p><br><h1><h2><h3><h4><ul><ol><li><strong><b><em><i><u><a><blockquote><table><thead><tbody><tfoot><tr><th><td><img><hr><span><div><pre><code><figure><figcaption>';
        $body = strip_tags($content, $allowed);
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
    <style>
        /* =====================================================================
   CUSTOM PAGES (pages.show)
   Append to resources/css/frontend.css. Scoped to .pg-page.
   No web fonts or external files.
   ===================================================================== */

.pg-page {
    --pg-ink: var(--ink, #0b1017);
    --pg-steel: #9aa6b4;
    --pg-text: #18202a;
    --pg-muted: #566170;
    --pg-mist: #f4f6f8;
    --pg-line: #e2e6ea;
    --pg-line-dark: rgba(255, 255, 255, .11);
    --pg-red: var(--brand-red, #e4002b);
    --pg-ease: cubic-bezier(.2, .7, .2, 1);
    color: var(--pg-text);
    overflow-x: clip;
}

.pg-page a:focus-visible,
.pg-page button:focus-visible {
    outline: 2px solid var(--pg-red);
    outline-offset: 3px;
}

.pg-page .btn {
    transition: transform .25s var(--pg-ease), box-shadow .25s var(--pg-ease), background-color .25s, color .25s, border-color .25s;
}

.pg-page .btn-brand:hover {
    transform: translateY(-2px);
    box-shadow: 0 14px 28px -14px rgba(228, 0, 43, .55);
}

/* ---- Dark hero with the animated grid ---- */
.pg-hero {
    position: relative;
    isolation: isolate;
    overflow: hidden;
    padding: clamp(2.5rem, 6vw, 4rem) 0 clamp(2.25rem, 5vw, 3.25rem);
    background: var(--pg-ink);
    color: #fff;
}

.pg-hero::before {
    content: "";
    position: absolute;
    inset: 0;
    z-index: -1;
    pointer-events: none;
    background-image:
        linear-gradient(rgba(255, 255, 255, .045) 1px, transparent 1px),
        linear-gradient(90deg, rgba(255, 255, 255, .045) 1px, transparent 1px);
    background-size: 52px 52px;
    -webkit-mask-image: radial-gradient(ellipse 70% 80% at 70% 30%, #000, transparent 75%);
    mask-image: radial-gradient(ellipse 70% 80% at 70% 30%, #000, transparent 75%);
    animation: pgDrift 50s linear infinite;
}

.pg-hero::after {
    content: "";
    position: absolute;
    z-index: -1;
    width: 32rem;
    height: 32rem;
    right: -10rem;
    top: -14rem;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(228, 0, 43, .22), transparent 65%);
    pointer-events: none;
}

.pg-crumbs {
    margin-bottom: 1.25rem;
    font-size: .85rem;
    color: var(--pg-steel);
}

.pg-crumbs a {
    color: var(--pg-steel);
    text-decoration: none;
    transition: color .2s;
}

.pg-crumbs a:hover {
    color: #fff;
}

.pg-hero h1 {
    max-width: 22ch;
    margin-bottom: .9rem;
    font-size: clamp(2rem, 4.4vw, 3.1rem);
    font-weight: 700;
    letter-spacing: -.02em;
    line-height: 1.1;
    color: #fff;
}

.pg-lede {
    max-width: 60ch;
    margin-bottom: 1.25rem;
    font-size: 1.1rem;
    line-height: 1.65;
    color: var(--pg-steel);
}

.pg-meta {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: .5rem 1.5rem;
    font-size: .9rem;
    color: var(--pg-steel);
}

.pg-meta i {
    margin-right: .4rem;
    color: #fff;
}

.pg-print {
    display: inline-flex;
    align-items: center;
    padding: .3rem .8rem;
    border: 1px solid rgba(255, 255, 255, .22);
    border-radius: 6px;
    background: transparent;
    font-size: .85rem;
    color: #fff;
    cursor: pointer;
    transition: background-color .25s, border-color .25s, color .25s;
}

.pg-print i {
    margin-right: .4rem;
}

.pg-print:hover {
    background: #fff;
    border-color: #fff;
    color: var(--pg-ink);
}

.pg-print:hover i {
    color: var(--pg-ink);
}

.pg-in > * {
    opacity: 0;
    transform: translateY(16px);
    animation: pgRise .7s var(--pg-ease) forwards;
    animation-delay: calc(var(--i, 0) * .09s + .05s);
}

.pg-body {
    padding-block: clamp(2rem, 5vw, 4rem);
}

/* ---- Sidebar ---- */
.pg-side {
    position: sticky;
    top: calc(var(--header-h, 80px) + 1.25rem);
}

.pg-card {
    margin-bottom: 1.25rem;
    padding: 1.25rem;
    border: 1px solid var(--pg-line);
    border-radius: 14px;
    background: #fff;
}

.pg-card-title {
    margin-bottom: .75rem;
    font-size: .95rem;
    font-weight: 700;
    letter-spacing: -.01em;
    color: var(--pg-ink);
}

.pg-nav,
.pg-toc {
    margin: 0;
    padding: 0;
    list-style: none;
}

.pg-nav a,
.pg-toc a {
    display: block;
    padding: .42rem 0 .42rem .9rem;
    border-left: 2px solid var(--pg-line);
    font-size: .9rem;
    line-height: 1.4;
    color: var(--pg-muted);
    text-decoration: none;
    transition: color .2s, border-color .2s, padding-left .3s var(--pg-ease);
}

.pg-toc a.sub {
    padding-left: 1.7rem;
    font-size: .85rem;
}

.pg-nav a:hover,
.pg-toc a:hover {
    padding-left: 1.15rem;
    color: var(--pg-ink);
}

.pg-toc a.sub:hover {
    padding-left: 1.95rem;
}

.pg-nav a.active,
.pg-toc a.active {
    border-left-color: var(--pg-red);
    color: var(--pg-ink);
    font-weight: 600;
}

/* ---- Article ---- */
.pg-cover {
    margin: 0 0 2rem;
    overflow: hidden;
    border-radius: 16px;
    background: var(--pg-mist);
    box-shadow: 0 30px 60px -40px rgba(11, 16, 23, .5);
}

.pg-cover img {
    display: block;
    width: 100%;
    height: auto;
}

.pg-content {
    font-size: 1.05rem;
    line-height: 1.85;
    color: #2b323b;
    overflow-wrap: anywhere;
    animation: pgRise .7s var(--pg-ease) .15s both;
}

.pg-content > *:first-child {
    margin-top: 0;
}

.pg-content p {
    margin-bottom: 1.3rem;
}

.pg-content h2,
.pg-content h3,
.pg-content h4 {
    margin: 2.2rem 0 .9rem;
    font-weight: 700;
    line-height: 1.3;
    letter-spacing: -.015em;
    color: var(--pg-ink);
    scroll-margin-top: calc(var(--header-h, 80px) + 1.25rem);
}

.pg-content h2 {
    font-size: 1.55rem;
}

.pg-content h3 {
    font-size: 1.25rem;
}

.pg-content a {
    color: var(--pg-red);
    text-decoration: underline;
    text-underline-offset: 3px;
    transition: color .2s;
}

.pg-content a:hover {
    color: var(--pg-ink);
}

.pg-content ul,
.pg-content ol {
    margin-bottom: 1.3rem;
    padding-left: 1.4rem;
}

.pg-content li {
    margin-bottom: .45rem;
}

.pg-content li::marker {
    color: var(--pg-red);
    font-weight: 700;
}

.pg-content img {
    max-width: 100%;
    height: auto;
    margin: 1.25rem 0;
    border-radius: 12px;
}

.pg-content blockquote {
    margin: 1.75rem 0;
    padding: 1.1rem 1.4rem;
    border-left: 3px solid var(--pg-red);
    border-radius: 0 12px 12px 0;
    background: var(--pg-mist);
    color: var(--pg-ink);
}

.pg-content blockquote > :last-child {
    margin-bottom: 0;
}

.pg-content pre {
    padding: 1rem 1.25rem;
    overflow-x: auto;
    border-radius: 12px;
    background: var(--pg-ink);
    color: #e5e7eb;
    font-size: .9rem;
}

.pg-content code {
    padding: .15rem .4rem;
    border-radius: 6px;
    background: var(--pg-mist);
    font-size: .9em;
}

.pg-content pre code {
    padding: 0;
    background: none;
}

.pg-content table {
    display: block;
    width: 100%;
    margin-bottom: 1.5rem;
    overflow-x: auto;
    border-collapse: collapse;
}

.pg-content th,
.pg-content td {
    padding: .65rem .85rem;
    border: 1px solid var(--pg-line);
}

.pg-content th {
    background: var(--pg-mist);
    text-align: left;
}

.pg-content hr {
    margin: 2rem 0;
    border-color: var(--pg-line);
    opacity: 1;
}

/* ---- Help block ---- */
.pg-help {
    position: relative;
    isolation: isolate;
    overflow: hidden;
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: 1.25rem 2rem;
    margin-top: 3rem;
    padding: clamp(1.5rem, 3vw, 2.25rem);
    border-radius: 16px;
    background: var(--pg-ink);
    color: #fff;
}

.pg-help::before {
    content: "";
    position: absolute;
    inset: 0;
    z-index: -1;
    pointer-events: none;
    background-image:
        linear-gradient(rgba(255, 255, 255, .045) 1px, transparent 1px),
        linear-gradient(90deg, rgba(255, 255, 255, .045) 1px, transparent 1px);
    background-size: 52px 52px;
    -webkit-mask-image: radial-gradient(ellipse 80% 120% at 80% 30%, #000, transparent 80%);
    mask-image: radial-gradient(ellipse 80% 120% at 80% 30%, #000, transparent 80%);
    animation: pgDrift 50s linear infinite;
}

.pg-help h2 {
    margin-bottom: .4rem;
    font-size: 1.3rem;
    font-weight: 700;
    letter-spacing: -.01em;
    color: #fff;
}

.pg-help p {
    margin-bottom: .4rem;
    color: var(--pg-steel);
}

.pg-help-links {
    display: flex;
    flex-wrap: wrap;
    gap: .35rem 1.5rem;
    margin: 0;
}

.pg-help-links a {
    color: #fff;
    text-decoration: none;
    background-image: linear-gradient(#fff, #fff);
    background-size: 0 1px;
    background-position: 0 100%;
    background-repeat: no-repeat;
    transition: background-size .3s var(--pg-ease);
}

.pg-help-links a:hover {
    background-size: 100% 1px;
}

.pg-help-links i {
    margin-right: .45rem;
}

@media (max-width: 991.98px) {
    .pg-side {
        position: static;
    }
}

@keyframes pgDrift {
    to { background-position: 52px 52px; }
}

@keyframes pgRise {
    from { opacity: 0; transform: translateY(16px); }
    to   { opacity: 1; transform: none; }
}

@media (prefers-reduced-motion: reduce) {
    .pg-page *,
    .pg-page *::before,
    .pg-page *::after {
        animation: none !important;
        transition: none !important;
    }

    .pg-in > * {
        opacity: 1;
        transform: none;
    }
}

/* ---- Print: just the page text ---- */
@media print {
    .site-header,
    .site-footer,
    .pg-side-col,
    .pg-help,
    .pg-print,
    .pg-hero::before,
    .pg-hero::after {
        display: none !important;
    }

    .pg-hero {
        padding: 0 0 1rem;
        background: none;
        color: #000;
    }

    .pg-hero h1,
    .pg-lede,
    .pg-meta,
    .pg-crumbs,
    .pg-crumbs a {
        color: #000;
    }

    .pg-in > * {
        opacity: 1;
        transform: none;
        animation: none;
    }

    .pg-content {
        font-size: 11pt;
        color: #000;
    }

    .pg-content a {
        color: #000;
        text-decoration: underline;
    }
}
    </style>

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
                    <a href="{{ route('contact.create') }}" class="btn btn-brand btn-lg">Contact us</a>
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
<style>

</style>
@endif
@endsection

