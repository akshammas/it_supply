@extends('frontend.layouts.app')
@section('title', 'Request a Quote | '.config('app.name'))

@section('content')
<div class="container py-4">
    <h2 class="mb-4">Request a Quote</h2>

    @if($errors->any())
        <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
    @endif

    <div class="row">
        <div class="col-lg-7 mb-4">
            <form method="POST" action="{{ route('quote-request.store') }}">
                @csrf
                <div class="card shadow-sm">
                    <div class="card-body row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Full Name *</label>
                            <input type="text" name="name" value="{{ old('name') }}" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Company Name</label>
                            <input type="text" name="company_name" value="{{ old('company_name') }}" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email *</label>
                            <input type="email" name="email" value="{{ old('email') }}" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Phone / WhatsApp *</label>
                            <input type="text" name="phone" value="{{ old('phone') }}" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Emirate</label>
                            <select name="emirate" class="form-select">
                                <option value="">— Select —</option>
                                @foreach(['Dubai','Abu Dhabi','Sharjah','Ajman','Ras Al Khaimah','Fujairah','Umm Al Quwain'] as $emirate)
                                    <option value="{{ $emirate }}" @selected(old('emirate')===$emirate)>{{ $emirate }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">TRN (optional)</label>
                            <input type="text" name="trn" value="{{ old('trn') }}" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Delivery Location</label>
                            <input type="text" name="delivery_location" value="{{ old('delivery_location') }}" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Required Delivery Date</label>
                            <input type="date" name="required_delivery_date" value="{{ old('required_delivery_date') }}" class="form-control">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Message</label>
                            <textarea name="message" rows="4" class="form-control" placeholder="Any additional details...">{{ old('message') }}</textarea>
                        </div>
                    </div>
                </div>
                <button class="btn btn-primary btn-lg mt-3">Submit Quote Request</button>
            </form>
        </div>

        <div class="col-lg-5">
            <div class="card shadow-sm">
                <div class="card-header bg-white fw-semibold">Your Enquiry List</div>
                <ul class="list-group list-group-flush">
                    @foreach($items as $item)
                        <li class="list-group-item d-flex justify-content-between">
                            <span>{{ $item['product']->name }}</span>
                            <span class="text-muted">x{{ $item['quantity'] }}</span>
                        </li>
                    @endforeach
                </ul>
                <div class="card-footer bg-white">
                    <a href="{{ route('enquiry.index') }}" class="small">Edit enquiry list</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
