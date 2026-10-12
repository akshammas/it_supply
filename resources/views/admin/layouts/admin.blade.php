<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin') | {{ config('app.name') }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @php
        $siteName = \App\Models\Setting::get('company_name') ?: config('app.name');
        $siteLogo = \App\Models\Setting::get('logo');
        $adminUser = auth()->user();
        $isSuper = $adminUser?->role === 'super_admin';
        $newEnquiries = \App\Models\Enquiry::where('status', 'new')->count();

        // [label, icon, route|null, [active patterns], badge|null, superOnly]
        $nav = [
            'Overview' => [
                ['Dashboard', 'bi-speedometer2', 'admin.dashboard', ['admin.dashboard']],
            ],
            'Catalogue' => [
                ['Products', 'bi-box-seam', 'admin.products.index', ['admin.products.*']],
                ['Categories', 'bi-diagram-3', 'admin.categories.index', ['admin.categories.*']],
                ['Brands', 'bi-award', 'admin.brands.index', ['admin.brands.*']],
                ['Specifications', 'bi-list-check', 'admin.specifications.index', ['admin.specifications.*', 'admin.specification-groups.*']],
                ['Import / Export', 'bi-upload', 'admin.imports.create', ['admin.imports.*']],
            ],
            'Sales' => [
                ['Enquiries', 'bi-envelope', 'admin.enquiries.index', ['admin.enquiries.*'], $newEnquiries],
                ['Quotes', 'bi-file-earmark-text', 'admin.quotes.index', ['admin.quotes.*']],
                ['Customers', 'bi-people', null, []],
            ],
            'Content' => [
                ['Blog', 'bi-journal-text', 'admin.blog.index', ['admin.blog.*', 'admin.blog-categories.*']],
                ['Pages', 'bi-file-earmark', 'admin.pages.index', ['admin.pages.*']],
                ['Banners', 'bi-images', 'admin.banners.index', ['admin.banners.*']],
            ],
            'System' => [
                ['Admin Users', 'bi-person-badge', null, [], null, true],
                ['Settings', 'bi-gear', 'admin.settings.edit', ['admin.settings.*']],
            ],
        ];
    @endphp

    <@vite(['resources/css/app.css', 'resources/css/admin.css', 'resources/js/app.js'])
    
</head>
<body class="admin-body">

<aside class="admin-sidebar" id="adminSidebar">
    <a href="{{ route('admin.dashboard') }}" class="side-brand">
        @if($siteLogo)
            <img src="{{ Storage::url($siteLogo) }}" alt="{{ $siteName }}">
        @else
            <span class="mark"><i class="bi bi-cpu"></i></span>
            <span class="name">{{ $siteName }}<small>Admin panel</small></span>
        @endif
    </a>

    <nav class="side-nav">
        @foreach($nav as $section => $items)
            @php $visible = collect($items)->filter(fn($i) => empty($i[5]) || $isSuper); @endphp
            @if($visible->isNotEmpty())
                <div class="side-label">{{ $section }}</div>
                @foreach($visible as $item)
                    @php
                        $active = $item[2] && request()->routeIs(...$item[3]);
                        $badge  = $item[4] ?? null;
                    @endphp
                    <a href="{{ $item[2] ? route($item[2]) : '#' }}"
                       class="side-link {{ $active ? 'active' : '' }} {{ $item[2] ? '' : 'disabled' }}">
                        <i class="bi {{ $item[1] }}"></i>
                        <span>{{ $item[0] }}</span>
                        @if($badge)
                            <span class="side-badge">{{ $badge }}</span>
                        @elseif(! $item[2])
                            <span class="side-badge soon">Soon</span>
                        @endif
                    </a>
                @endforeach
            @endif
        @endforeach
    </nav>

    <div class="side-user">
        <span class="avatar">{{ strtoupper(mb_substr($adminUser?->name ?? 'A', 0, 1)) }}</span>
        <div class="who">
            <strong>{{ $adminUser?->name }}</strong>
            <span>{{ str_replace('_', ' ', $adminUser?->role ?? 'admin') }}</span>
        </div>
        <form method="POST" action="{{ route('admin.logout') }}">
            @csrf
            <button type="submit" class="logout" title="Sign out" aria-label="Sign out"><i class="bi bi-box-arrow-right"></i></button>
        </form>
    </div>
</aside>
<div class="admin-overlay" id="adminOverlay"></div>

<div class="admin-shell">
    <header class="admin-topbar">
        <button class="menu-btn" id="menuBtn" aria-label="Open menu"><i class="bi bi-list"></i></button>
        <div class="crumb">
            Admin <i class="bi bi-chevron-right mx-1" style="font-size:.7rem"></i> <strong>@yield('title', 'Dashboard')</strong>
        </div>
        <div class="top-actions">
            <a href="{{ route('home') }}" target="_blank" rel="noopener" class="top-link"><i class="bi bi-box-arrow-up-right"></i><span>View site</span></a>
            <div class="top-user">
                <span class="avatar">{{ strtoupper(mb_substr($adminUser?->name ?? 'A', 0, 1)) }}</span>
                <span>{{ $adminUser?->name }}</span>
            </div>
        </div>
    </header>

    <main class="admin-main">
        @if(session('status'))
            <div class="alert alert-success d-flex align-items-center gap-2">
                <i class="bi bi-check-circle-fill"></i> {{ session('status') }}
            </div>
        @endif

        @yield('content')
    </main>
</div>

<script>
    (function () {
        const body = document.body;
        document.getElementById('menuBtn').addEventListener('click', () => body.classList.toggle('sidebar-open'));
        document.getElementById('adminOverlay').addEventListener('click', () => body.classList.remove('sidebar-open'));
        document.addEventListener('keydown', e => { if (e.key === 'Escape') body.classList.remove('sidebar-open'); });
    })();
</script>
@yield('scripts')
</body>
</html>