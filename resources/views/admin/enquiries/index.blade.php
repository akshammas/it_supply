@extends('admin.layouts.admin')
@section('title', 'Enquiries')

@section('content')
    <h3 class="mb-4">Enquiries</h3>

    @if(session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif

    <form method="GET" class="mb-3">
        <select name="status" class="form-select w-auto" onchange="this.form.submit()">
            <option value="">All Statuses</option>
            @foreach($statuses as $status)
                <option value="{{ $status }}" @selected(request('status')===$status)>{{ str_replace('_',' ', ucfirst($status)) }}</option>
            @endforeach
        </select>
    </form>

    <div class="card shadow-sm">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light">
                <tr><th>#</th><th>Customer</th><th>Products</th><th>Status</th><th>Date</th><th class="text-end">Actions</th></tr>
            </thead>
            <tbody>
                @foreach($enquiries as $enquiry)
                    <tr>
                        <td>#{{ $enquiry->id }}</td>
                        <td>
                            {{ $enquiry->company_name ?? $enquiry->name }}
                            <div class="text-muted small">{{ $enquiry->email }} &middot; {{ $enquiry->phone }}</div>
                        </td>
                        <td>{{ $enquiry->items_count }}</td>
                        <td><span class="badge bg-secondary text-capitalize">{{ str_replace('_',' ', $enquiry->status) }}</span></td>
                        <td>{{ $enquiry->created_at->format('d M Y') }}</td>
                        <td class="text-end">
                            <a href="{{ route('admin.enquiries.show', $enquiry) }}" class="btn btn-sm btn-outline-secondary">View</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-3">{{ $enquiries->links() }}</div>
@endsection
