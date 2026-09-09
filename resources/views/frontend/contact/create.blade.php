@extends('frontend.layouts.app')
@section('title', 'Contact Us | '.config('app.name'))

@section('content')
<div class="container py-4" style="max-width:700px;">
    <h2 class="mb-4">Contact Us</h2>

    @if(session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif
    @if($errors->any())
        <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
    @endif

    <form method="POST" action="{{ route('contact.store') }}">
        @csrf
        <div class="card shadow-sm">
            <div class="card-body row g-3">
                <div class="col-md-6">
                    <label class="form-label">Name *</label>
                    <input type="text" name="name" value="{{ old('name') }}" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Email *</label>
                    <input type="email" name="email" value="{{ old('email') }}" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Phone</label>
                    <input type="text" name="phone" value="{{ old('phone') }}" class="form-control">
                </div>
                <div class="col-12">
                    <label class="form-label">Message *</label>
                    <textarea name="message" rows="5" class="form-control" required>{{ old('message') }}</textarea>
                </div>
            </div>
        </div>
        <button class="btn btn-primary btn-lg mt-3">Send Message</button>
    </form>
</div>
@endsection
