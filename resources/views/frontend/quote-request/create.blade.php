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
                    <div class="col-12">
                        <label class="form-label">Full Name *</label>
                        <input type="text" name="name" value="{{ old('name') }}" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Email *</label>
                        <input type="email" name="email" value="{{ old('email') }}" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Phone / WhatsApp *</label>
                        <input type="text" name="phone" value="{{ old('phone') }}" class="form-control" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Subject *</label>
                        <input type="text" name="subject" value="{{ old('subject', $defaultSubject) }}" class="form-control"
                              placeholder="e.g. Quote for 10 Lenovo monitors" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Message</label>
                        <textarea name="message" rows="6" class="form-control" placeholder="Any additional details...">{{ old('message', $defaultMessage) }}</textarea>
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
