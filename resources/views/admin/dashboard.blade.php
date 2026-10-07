@extends('admin.layouts.admin')

@section('title', 'Dashboard')

@section('content')
@php
    $hour = (int) now()->format('G');
    $greet = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');
    $firstName = explode(' ', trim(auth()->user()?->name ?? 'there'))[0];

    $cards = [
        ['Products',     $stats['products'],      'bi-box-seam',          'admin.products.index',   'blue'],
        ['Categories',   $stats['categories'],    'bi-diagram-3',         'admin.categories.index', 'violet'],
        ['Brands',       $stats['brands'],        'bi-award',             'admin.brands.index',     'teal'],
        ['Enquiries',    $stats['enquiries'],     'bi-envelope',          'admin.enquiries.index',  'slate'],
        ['New enquiries',$stats['new_enquiries'], 'bi-bell',              'admin.enquiries.index',  'red'],
        ['Quotes',       $stats['quotes'],        'bi-file-earmark-text', 'admin.quotes.index',     'amber'],
    ];
    $chartMax = max(1, $chart->max('count'));
@endphp

<style>
    .dash-hero {
        position: relative; overflow: hidden; border-radius: 18px; padding: 1.75rem 2rem; color: #fff;
        background: linear-gradient(135deg, #15171a 0%, #23272c 55%, #3a0a14 100%);
    }
    .dash-hero::after {
        content: ""; position: absolute; right: -80px; top: -120px; width: 380px; height: 380px; border-radius: 50%;
        background: radial-gradient(circle, rgba(228,0,43,.5), transparent 65%); pointer-events: none;
    }
    .dash-hero > * { position: relative; z-index: 1; }
    .dash-hero h2 { font-weight: 800; letter-spacing: -.02em; margin-bottom: .25rem; }
    .dash-hero p { color: rgba(255,255,255,.65); margin-bottom: 1.1rem; }
    .quick-btn {
        display: inline-flex; align-items: center; gap: .45rem; padding: .55rem 1rem; border-radius: 10px;
        font-weight: 600; font-size: .88rem; text-decoration: none; transition: .15s;
        background: rgba(255,255,255,.1); color: #fff; border: 1px solid rgba(255,255,255,.16);
    }
    .quick-btn:hover { background: #fff; color: var(--ink); }
    .quick-btn.primary { background: var(--brand-red); border-color: var(--brand-red); }
    .quick-btn.primary:hover { background: var(--brand-red-dark); border-color: var(--brand-red-dark); color: #fff; }

    .stat-card {
        display: flex; align-items: center; gap: .9rem; padding: 1.1rem 1.15rem; height: 100%;
        background: #fff; border: 1px solid #eceef1; border-radius: 14px; text-decoration: none; color: var(--ink);
        transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease;
    }
    .stat-card:hover { transform: translateY(-3px); box-shadow: 0 14px 28px -16px rgba(16,24,40,.25); border-color: #e0e3e8; color: var(--ink); }
    .stat-icon { width: 46px; height: 46px; border-radius: 13px; flex-shrink: 0; display: inline-flex; align-items: center; justify-content: center; font-size: 1.25rem; }
    .stat-num { font-size: 1.6rem; font-weight: 800; line-height: 1; letter-spacing: -.02em; }
    .stat-lbl { color: var(--muted); font-size: .8rem; font-weight: 500; margin-top: .25rem; }
    .t-blue   { background: #e8f0ff; color: #2563eb; }
    .t-violet { background: #f1eaff; color: #7c3aed; }
    .t-teal   { background: #dff7f2; color: #0f9d85; }
    .t-slate  { background: #eef0f3; color: #475467; }
    .t-red    { background: var(--brand-red-light); color: var(--brand-red); }
    .t-amber  { background: #fff4e0; color: #d97706; }
    .stat-card.is-alert { border-color: #ffd3da; background: linear-gradient(180deg, #fff, #fff7f8); }

    .enq-row { display: flex; align-items: center; gap: .9rem; padding: .85rem 1.15rem; text-decoration: none; color: var(--ink); border-bottom: 1px solid #f2f3f5; transition: background .15s; }
    .enq-row:last-child { border-bottom: 0; }
    .enq-row:hover { background: #fafbfc; color: var(--ink); }
    .enq-avatar { width: 40px; height: 40px; border-radius: 12px; background: #f1f3f5; color: #475467; font-weight: 700; display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0; }
    .enq-main { min-width: 0; flex: 1; }
    .enq-main .t { font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .enq-main .s { color: var(--muted); font-size: .8rem; }

    .bars { display: flex; align-items: flex-end; gap: .6rem; height: 150px; padding-top: 1rem; }
    .bar-col { flex: 1; display: flex; flex-direction: column; align-items: center; justify-content: flex-end; height: 100%; gap: .4rem; }
    .bar-num { font-size: .75rem; font-weight: 700; color: var(--ink); }
    .bar { width: 100%; max-width: 38px; border-radius: 8px 8px 4px 4px; background: linear-gradient(180deg, var(--brand-red), var(--brand-red-dark)); min-height: 5px; transition: opacity .15s; }
    .bar.zero { background: #e9ebee; }
    .bar-col:hover .bar { opacity: .8; }
    .bar-day { font-size: .72rem; color: var(--muted); font-weight: 600; }

    .act-item { display: flex; gap: .8rem; padding: .8rem 1.15rem; border-bottom: 1px solid #f2f3f5; }
    .act-item:last-child { border-bottom: 0; }
    .act-dot { width: 32px; height: 32px; border-radius: 10px; flex-shrink: 0; display: inline-flex; align-items: center; justify-content: center; font-size: .95rem; }
    .a-created { background: #dcfce7; color: #15803d; }
    .a-updated { background: #e0f2fe; color: #0369a1; }
    .a-deleted { background: #fee2e2; color: #b91c1c; }
    .a-other   { background: #f1f3f5; color: #475467; }
    .act-text { font-size: .86rem; line-height: 1.35; }
    .act-time { color: var(--muted); font-size: .76rem; }
    .empty { padding: 2.2rem 1rem; text-align: center; color: var(--muted); }
    .empty i { font-size: 1.8rem; display: block; margin-bottom: .4rem; opacity: .5; }
</style>

{{-- Hero --}}
<div class="dash-hero mb-4">
    <h2>{{ $greet }}, {{ $firstName }} 👋</h2>
    <p>Here's what's happening in your store today.</p>
    <div class="d-flex flex-wrap gap-2">
        <a href="{{ route('admin.products.create') }}" class="quick-btn primary"><i class="bi bi-plus-lg"></i> Add product</a>
        <a href="{{ route('admin.banners.create') }}" class="quick-btn"><i class="bi bi-images"></i> Add banner</a>
        <a href="{{ route('admin.brands.create') }}" class="quick-btn"><i class="bi bi-award"></i> Add brand</a>
        <a href="{{ route('admin.enquiries.index') }}" class="quick-btn"><i class="bi bi-envelope"></i> View enquiries</a>
    </div>
</div>

{{-- Stat cards --}}
<div class="row g-3 mb-4">
    @foreach($cards as [$label, $value, $icon, $route, $tone])
        <div class="col-6 col-md-4 col-xl-2">
            <a href="{{ route($route) }}" class="stat-card {{ $tone === 'red' && $value > 0 ? 'is-alert' : '' }}">
                <span class="stat-icon t-{{ $tone }}"><i class="bi {{ $icon }}"></i></span>
                <div>
                    <div class="stat-num">{{ number_format($value) }}</div>
                    <div class="stat-lbl">{{ $label }}</div>
                </div>
            </a>
        </div>
    @endforeach
</div>

<div class="row g-3">
    {{-- Left: chart + recent enquiries --}}
    <div class="col-xl-7">
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>Enquiries · last 7 days</span>
                <span class="text-muted small fw-normal">{{ $chart->sum('count') }} total</span>
            </div>
            <div class="card-body pt-0">
                <div class="bars">
                    @foreach($chart as $day)
                        <div class="bar-col" title="{{ $day['date'] }}: {{ $day['count'] }} enquiries">
                            <span class="bar-num">{{ $day['count'] }}</span>
                            <div class="bar {{ $day['count'] === 0 ? 'zero' : '' }}"
                                 style="height: {{ $day['count'] === 0 ? 5 : max(8, round($day['count'] / $chartMax * 100)) }}%"></div>
                            <span class="bar-day">{{ $day['label'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>Recent enquiries</span>
                <a href="{{ route('admin.enquiries.index') }}" class="small fw-semibold text-decoration-none" style="color:var(--brand-red)">View all →</a>
            </div>
            @forelse($recentEnquiries as $enquiry)
                @php $who = $enquiry->company_name ?? $enquiry->name; @endphp
                <a href="{{ route('admin.enquiries.show', $enquiry) }}" class="enq-row">
                    <span class="enq-avatar">{{ strtoupper(mb_substr($who, 0, 1)) }}</span>
                    <div class="enq-main">
                        <div class="t">#{{ $enquiry->id }} · {{ $who }}</div>
                        <div class="s">{{ $enquiry->items_count }} product(s) · {{ $enquiry->created_at->diffForHumans() }}</div>
                    </div>
                    <span class="status-pill s-{{ $enquiry->status }}">{{ str_replace('_', ' ', $enquiry->status) }}</span>
                </a>
            @empty
                <div class="empty"><i class="bi bi-inbox"></i>No enquiries yet.</div>
            @endforelse
        </div>
    </div>

    {{-- Right: activity --}}
    <div class="col-xl-5">
        <div class="card">
            <div class="card-header">Recent activity</div>
            @forelse($recentActivity as $log)
                @php
                    $act = strtolower($log->action);
                    $cls = str_contains($act, 'creat') ? 'a-created' : (str_contains($act, 'updat') ? 'a-updated' : (str_contains($act, 'delet') ? 'a-deleted' : 'a-other'));
                    $ico = $cls === 'a-created' ? 'bi-plus-lg' : ($cls === 'a-updated' ? 'bi-pencil' : ($cls === 'a-deleted' ? 'bi-trash' : 'bi-lightning'));
                @endphp
                <div class="act-item">
                    <span class="act-dot {{ $cls }}"><i class="bi {{ $ico }}"></i></span>
                    <div>
                        <div class="act-text">
                            <strong>{{ $log->user?->name ?? 'System' }}</strong>
                            {{ $log->action }} <span class="text-capitalize">{{ $log->module }}</span> #{{ $log->record_id }}
                        </div>
                        <div class="act-time">{{ $log->created_at->diffForHumans() }}</div>
                    </div>
                </div>
            @empty
                <div class="empty"><i class="bi bi-clock-history"></i>No activity yet.</div>
            @endforelse
        </div>
    </div>
</div>
@endsection