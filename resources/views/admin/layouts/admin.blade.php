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

    <style>
        :root {
            --brand-red: #E4002B;
            --brand-red-dark: #B5001F;
            --brand-red-light: #FFF1F3;
            --ink: #1A1A1A;
            --muted: #6B7280;
            --side-bg: #15171a;
            --side-w: 260px;
        }

        .admin-body { font-family: 'Inter', system-ui, sans-serif; color: var(--ink); background: #f5f6f8; }

        /* ============ Sidebar ============ */
        .admin-sidebar {
            position: fixed; inset: 0 auto 0 0; width: var(--side-w); z-index: 1040;
            background: var(--side-bg); color: #fff;
            display: flex; flex-direction: column;
            transition: transform .25s ease;
        }
        .side-brand {
            display: flex; align-items: center; gap: .75rem; padding: 1.1rem 1.25rem;
            text-decoration: none; color: #fff; border-bottom: 1px solid rgba(255,255,255,.07);
        }
        .side-brand .mark {
            width: 38px; height: 38px; border-radius: 11px; background: var(--brand-red);
            display: inline-flex; align-items: center; justify-content: center; font-size: 1.15rem; flex-shrink: 0;
        }
        .side-brand img { max-height: 36px; max-width: 150px; background: #fff; border-radius: 8px; padding: 4px 8px; }
        .side-brand .name { font-weight: 800; letter-spacing: -.01em; line-height: 1.1; }
        .side-brand small { display: block; color: rgba(255,255,255,.45); font-weight: 500; font-size: .7rem; letter-spacing: .06em; text-transform: uppercase; }

        .side-nav { flex: 1; overflow-y: auto; padding: .75rem .75rem 1rem; scrollbar-width: thin; scrollbar-color: #333 transparent; }
        .side-label {
            padding: 1rem .75rem .4rem; font-size: .68rem; font-weight: 700;
            text-transform: uppercase; letter-spacing: .09em; color: rgba(255,255,255,.35);
        }
        .side-link {
            position: relative; display: flex; align-items: center; gap: .75rem;
            padding: .6rem .75rem; margin-bottom: 2px; border-radius: 10px;
            color: rgba(255,255,255,.65); text-decoration: none; font-size: .9rem; font-weight: 500;
            transition: background .15s ease, color .15s ease, padding-left .15s ease;
        }
        .side-link i { font-size: 1.05rem; width: 20px; text-align: center; transition: color .15s ease; }
        .side-link:hover { background: rgba(255,255,255,.07); color: #fff; padding-left: .95rem; }
        .side-link.active { background: rgba(228,0,43,.16); color: #fff; font-weight: 600; }
        .side-link.active i { color: #ff5470; }
        .side-link.active::before {
            content: ""; position: absolute; left: -.75rem; top: 20%; bottom: 20%;
            width: 4px; border-radius: 0 4px 4px 0; background: var(--brand-red);
        }
        .side-link.disabled { opacity: .45; pointer-events: none; }
        .side-badge {
            margin-left: auto; background: var(--brand-red); color: #fff; font-size: .68rem; font-weight: 700;
            min-width: 20px; height: 20px; padding: 0 6px; border-radius: 10px;
            display: inline-flex; align-items: center; justify-content: center;
        }
        .side-badge.soon { background: rgba(255,255,255,.12); font-weight: 600; }

        .side-user {
            display: flex; align-items: center; gap: .7rem; padding: .9rem 1.1rem;
            border-top: 1px solid rgba(255,255,255,.07);
        }
        .avatar {
            width: 38px; height: 38px; border-radius: 50%; flex-shrink: 0;
            background: linear-gradient(135deg, var(--brand-red), var(--brand-red-dark));
            display: inline-flex; align-items: center; justify-content: center; font-weight: 700; color: #fff;
        }
        .side-user .who { min-width: 0; flex: 1; line-height: 1.2; }
        .side-user .who strong { display: block; font-size: .88rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .side-user .who span { font-size: .72rem; color: rgba(255,255,255,.45); text-transform: capitalize; }
        .side-user .logout {
            border: 0; background: rgba(255,255,255,.07); color: rgba(255,255,255,.7);
            width: 36px; height: 36px; border-radius: 10px; transition: .15s;
        }
        .side-user .logout:hover { background: var(--brand-red); color: #fff; }

        .admin-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,.5); z-index: 1035; }

        /* ============ Shell + Top bar ============ */
        .admin-shell { margin-left: var(--side-w); min-height: 100vh; display: flex; flex-direction: column; }
        .admin-topbar {
            position: sticky; top: 0; z-index: 1020; height: 64px; padding: 0 1.75rem;
            display: flex; align-items: center; gap: 1rem;
            background: rgba(255,255,255,.88); backdrop-filter: blur(10px);
            border-bottom: 1px solid #eceef1;
        }
        .admin-topbar .menu-btn {
            display: none; border: 0; background: #f1f3f5; width: 40px; height: 40px; border-radius: 10px; font-size: 1.3rem;
        }
        .crumb { font-size: .85rem; color: var(--muted); }
        .crumb strong { color: var(--ink); font-weight: 700; font-size: 1rem; }
        .top-actions { margin-left: auto; display: flex; align-items: center; gap: .6rem; }
        .top-link {
            display: inline-flex; align-items: center; gap: .4rem; padding: .45rem .85rem; border-radius: 10px;
            border: 1px solid #e3e6ea; background: #fff; color: var(--ink); text-decoration: none; font-size: .85rem; font-weight: 600;
            transition: .15s;
        }
        .top-link:hover { border-color: var(--ink); color: var(--ink); }
        .top-user { display: flex; align-items: center; gap: .55rem; font-size: .88rem; font-weight: 600; }
        .top-user .avatar { width: 34px; height: 34px; font-size: .85rem; }

        .admin-main { flex: 1; padding: 1.75rem; max-width: 1500px; width: 100%; }

        /* ============ Global polish for every admin page ============ */
        .admin-body .card { border: 1px solid #eceef1; border-radius: 14px; box-shadow: 0 1px 2px rgba(16,24,40,.04); }
        .admin-body .card.shadow-sm { box-shadow: 0 1px 2px rgba(16,24,40,.04) !important; }
        .admin-body .card-header { background: #fff; border-bottom: 1px solid #f0f1f4; border-radius: 14px 14px 0 0 !important; padding: .9rem 1.15rem; }
        .admin-body .card-header.bg-white { font-weight: 700; }
        .admin-body h3 { font-weight: 800; letter-spacing: -.02em; }

        .admin-body .table > :not(caption) > * > * { padding: .8rem 1rem; }
        .admin-body .table thead th, .admin-body .table-light th {
            background: #fafbfc !important; color: var(--muted); font-size: .72rem; font-weight: 700;
            text-transform: uppercase; letter-spacing: .06em; border-bottom: 1px solid #eceef1;
        }
        .admin-body .table-hover > tbody > tr:hover > * { background: #fafbfc; }
        .admin-body .table tbody td { border-color: #f0f1f4; }

        .admin-body .btn { border-radius: 10px; font-weight: 600; }
        .admin-body .btn-primary {
            --bs-btn-bg: var(--brand-red); --bs-btn-border-color: var(--brand-red);
            --bs-btn-hover-bg: var(--brand-red-dark); --bs-btn-hover-border-color: var(--brand-red-dark);
            --bs-btn-active-bg: var(--brand-red-dark); --bs-btn-active-border-color: var(--brand-red-dark);
            --bs-btn-disabled-bg: var(--brand-red); --bs-btn-disabled-border-color: var(--brand-red);
            --bs-btn-focus-shadow-rgb: 228,0,43;
        }
        .admin-body .btn-outline-secondary { --bs-btn-color: #4b5563; --bs-btn-border-color: #dfe3e8; --bs-btn-hover-bg: #f3f4f6; --bs-btn-hover-color: var(--ink); --bs-btn-hover-border-color: #cfd4da; }

        .admin-body .form-control, .admin-body .form-select { border-radius: 10px; border-color: #e3e6ea; }
        .admin-body .form-control:focus, .admin-body .form-select:focus {
            border-color: var(--brand-red); box-shadow: 0 0 0 .2rem rgba(228,0,43,.12);
        }
        .admin-body .form-check-input:checked { background-color: var(--brand-red); border-color: var(--brand-red); }
        .admin-body .form-check-input:focus { border-color: var(--brand-red); box-shadow: 0 0 0 .2rem rgba(228,0,43,.12); }
        .admin-body .form-label { font-weight: 600; font-size: .85rem; }
        .admin-body .pagination { gap: 4px; margin: 0; flex-wrap: wrap; }
        .admin-body .page-link {
            color: var(--ink); border: 1px solid #e3e6ea; border-radius: 10px !important;
            min-width: 38px; text-align: center; font-weight: 600; font-size: .88rem; padding: .45rem .75rem;
        }
        .admin-body .page-link:hover { background: var(--brand-red-light); border-color: #ffd3da; color: var(--brand-red); }
        .admin-body .page-link:focus { box-shadow: 0 0 0 .2rem rgba(228,0,43,.12); }
        .admin-body .page-item.active .page-link { background: var(--brand-red); border-color: var(--brand-red); color: #fff; }
        .admin-body .page-item.disabled .page-link { background: #f5f6f8; color: #aab0b8; }
        .admin-body nav .small.text-muted { font-size: .82rem; }
        .admin-body .alert { border-radius: 12px; border: 0; }
        .admin-body .badge { font-weight: 600; }

        /* Soft status pills (reusable: <span class="status-pill s-new">New</span>) */
        .status-pill { display: inline-block; padding: .25rem .65rem; border-radius: 999px; font-size: .72rem; font-weight: 700; text-transform: capitalize; }
        .s-new            { background: #fff4e5; color: #b45309; }
        .s-contacted      { background: #e0f2fe; color: #0369a1; }
        .s-quotation_sent { background: #ede9fe; color: #6d28d9; }
        .s-negotiating    { background: #fef9c3; color: #a16207; }
        .s-won            { background: #dcfce7; color: #15803d; }
        .s-lost           { background: #fee2e2; color: #b91c1c; }
        .s-cancelled      { background: #f3f4f6; color: #4b5563; }

        /* ============ Mobile ============ */
        @media (max-width: 991px) {
            .admin-sidebar { transform: translateX(-100%); }
            .sidebar-open .admin-sidebar { transform: none; box-shadow: 0 0 40px rgba(0,0,0,.4); }
            .sidebar-open .admin-overlay { display: block; }
            .admin-shell { margin-left: 0; }
            .admin-topbar { padding: 0 1rem; }
            .admin-topbar .menu-btn { display: inline-flex; align-items: center; justify-content: center; }
            .admin-main { padding: 1.1rem; }
            .top-user span, .top-link span { display: none; }
        }
        @media (prefers-reduced-motion: reduce) {
            .admin-sidebar, .side-link { transition: none; }
        }
    </style>
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