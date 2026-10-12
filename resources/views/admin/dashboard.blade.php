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