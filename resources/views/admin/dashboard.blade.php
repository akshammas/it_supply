@extends('admin.layouts.admin')

@section('title', 'Dashboard')

@section('content')
    <h3 class="mb-4">Dashboard</h3>

    <div class="row g-3 mb-4">
        <div class="col-md-4 col-lg-2">
            <div class="card text-center shadow-sm">
                <div class="card-body">
                    <div class="fs-3 fw-bold">{{ $stats['products'] }}</div>
                    <div class="text-muted small">Products</div>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-lg-2">
            <div class="card text-center shadow-sm">
                <div class="card-body">
                    <div class="fs-3 fw-bold">{{ $stats['categories'] }}</div>
                    <div class="text-muted small">Categories</div>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-lg-2">
            <div class="card text-center shadow-sm">
                <div class="card-body">
                    <div class="fs-3 fw-bold">{{ $stats['brands'] }}</div>
                    <div class="text-muted small">Brands</div>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-lg-2">
            <div class="card text-center shadow-sm">
                <div class="card-body">
                    <div class="fs-3 fw-bold">{{ $stats['enquiries'] }}</div>
                    <div class="text-muted small">Enquiries</div>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-lg-2">
            <div class="card text-center shadow-sm border-warning">
                <div class="card-body">
                    <div class="fs-3 fw-bold text-warning">{{ $stats['new_enquiries'] }}</div>
                    <div class="text-muted small">New Enquiries</div>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-lg-2">
            <div class="card text-center shadow-sm">
                <div class="card-body">
                    <div class="fs-3 fw-bold">{{ $stats['quotes'] }}</div>
                    <div class="text-muted small">Quotes</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-7">
            <div class="card shadow-sm">
                <div class="card-header bg-white fw-semibold">Recent Enquiries</div>
                <ul class="list-group list-group-flush">
                    @forelse($recentEnquiries as $enquiry)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <div class="fw-semibold">#{{ $enquiry->id }} {{ $enquiry->company_name ?? $enquiry->name }}</div>
                                <div class="text-muted small">{{ $enquiry->items_count }} product(s) &middot; {{ $enquiry->created_at->format('d M Y') }}</div>
                            </div>
                            <span class="badge bg-secondary text-capitalize">{{ str_replace('_', ' ', $enquiry->status) }}</span>
                        </li>
                    @empty
                        <li class="list-group-item text-muted">No enquiries yet.</li>
                    @endforelse
                </ul>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card shadow-sm">
                <div class="card-header bg-white fw-semibold">Recent Activity</div>
                <ul class="list-group list-group-flush">
                    @forelse($recentActivity as $log)
                        <li class="list-group-item small">
                            <span class="fw-semibold">{{ $log->user?->name ?? 'System' }}</span>
                            {{ $log->action }} {{ $log->module }} #{{ $log->record_id }}
                            <div class="text-muted">{{ $log->created_at->diffForHumans() }}</div>
                        </li>
                    @empty
                        <li class="list-group-item text-muted">No activity yet.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
@endsection
