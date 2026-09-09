@extends('admin.layouts.admin')
@section('title', 'Enquiry #'.$enquiry->id)

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0">Enquiry #{{ $enquiry->id }}</h3>
        <a href="{{ route('admin.enquiries.index') }}" class="btn btn-outline-secondary btn-sm">Back to list</a>
    </div>

    @if(session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-sm mb-3">
                <div class="card-header bg-white fw-semibold">Products Requested</div>
                <table class="table mb-0">
                    <thead class="table-light"><tr><th>Product</th><th>SKU</th><th>Qty</th></tr></thead>
                    <tbody>
                        @forelse($enquiry->items as $item)
                            <tr>
                                <td>{{ $item->product?->name ?? 'Product removed' }}</td>
                                <td>{{ $item->product?->sku }}</td>
                                <td>{{ $item->quantity }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="text-muted">General enquiry — no products attached.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($enquiry->message)
                <div class="card shadow-sm mb-3">
                    <div class="card-header bg-white fw-semibold">Message</div>
                    <div class="card-body">{{ $enquiry->message }}</div>
                </div>
            @endif
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm mb-3">
                <div class="card-header bg-white fw-semibold">Customer</div>
                <div class="card-body">
                    <div><strong>{{ $enquiry->name }}</strong></div>
                    @if($enquiry->company_name)<div>{{ $enquiry->company_name }}</div>@endif
                    <div class="text-muted small mt-2">{{ $enquiry->email }}</div>
                    <div class="text-muted small">{{ $enquiry->phone }}</div>
                    @if($enquiry->delivery_location)<div class="text-muted small mt-2">Delivery: {{ $enquiry->delivery_location }}</div>@endif
                    @if($enquiry->required_delivery_date)<div class="text-muted small">Required by: {{ $enquiry->required_delivery_date->format('d M Y') }}</div>@endif
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-header bg-white fw-semibold">Status</div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.enquiries.status', $enquiry) }}">
                        @csrf @method('PUT')
                        <select name="status" class="form-select mb-2">
                            @foreach($statuses as $status)
                                <option value="{{ $status }}" @selected($enquiry->status===$status)>{{ str_replace('_',' ', ucfirst($status)) }}</option>
                            @endforeach
                        </select>
                        <button class="btn btn-primary w-100">Update Status</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
