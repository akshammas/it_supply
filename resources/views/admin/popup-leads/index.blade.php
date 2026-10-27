@extends('admin.layouts.admin')
@section('title', 'Popup Leads')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0">Popup Leads @if($newCount)<span class="badge bg-danger ms-2">{{ $newCount }} new</span>@endif</h3>
        <a href="{{ route('admin.popup-leads.export') }}" class="btn btn-outline-secondary"><i class="bi bi-download me-1"></i>Export CSV</a>
    </div>
    @if(session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif

    <form method="GET" class="row g-2 mb-3">
        <div class="col-md-5">
            <input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="Search name or phone">
        </div>
        <div class="col-md-3">
            <select name="status" class="form-select">
                <option value="">All</option>
                <option value="new" @selected(request('status') === 'new')>New</option>
                <option value="contacted" @selected(request('status') === 'contacted')>Contacted</option>
            </select>
        </div>
        <div class="col-md-auto">
            <button class="btn btn-outline-secondary">Filter</button>
            @if(request()->hasAny(['q','status']))<a href="{{ route('admin.popup-leads.index') }}" class="btn btn-link">Clear</a>@endif
        </div>
    </form>

    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr><th>Received</th><th>Name</th><th>Phone</th><th>Popup</th><th>Status</th><th class="text-end">Actions</th></tr>
                </thead>
                <tbody>
                    @forelse($leads as $lead)
                        <tr>
                            <td class="small text-muted text-nowrap">{{ $lead->created_at->format('d M Y, H:i') }}</td>
                            <td class="fw-semibold">{{ $lead->name }}</td>
                            <td class="text-nowrap">
                                <a href="tel:{{ $lead->phone }}" class="text-decoration-none">{{ $lead->phone }}</a>
                                <a href="https://wa.me/{{ $lead->whatsapp_number }}" target="_blank" class="ms-2 text-success" title="Open in WhatsApp"><i class="bi bi-whatsapp"></i></a>
                            </td>
                            <td class="small">
                                {{ $lead->popup?->name ?? 'Deleted popup' }}
                                @if($lead->page_url)<div class="text-muted text-truncate" style="max-width:220px" title="{{ $lead->page_url }}">{{ $lead->page_url }}</div>@endif
                            </td>
                            <td><span class="badge {{ $lead->status === 'new' ? 'bg-danger' : 'bg-success' }}">{{ ucfirst($lead->status) }}</span></td>
                            <td class="text-end text-nowrap">
                                <form action="{{ route('admin.popup-leads.status', $lead) }}" method="POST" class="d-inline">
                                    @csrf @method('PATCH')
                                    <button class="btn btn-sm btn-outline-secondary">{{ $lead->status === 'new' ? 'Mark contacted' : 'Mark new' }}</button>
                                </form>
                                <form action="{{ route('admin.popup-leads.destroy', $lead) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this lead?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-4">No leads yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-3">{{ $leads->links() }}</div>
@endsection