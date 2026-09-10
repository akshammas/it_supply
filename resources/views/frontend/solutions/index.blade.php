@extends('frontend.layouts.app')
@section('title', 'Solutions | '.config('app.name'))

@section('content')
<div class="container py-4">
    <h2 class="mb-4">Solutions</h2>

    @if($solutions->isEmpty())
        <p class="text-muted">No solutions published yet.</p>
    @else
        <div class="row">
            @foreach($solutions as $solution)
                <div class="col-md-4 mb-4">
                    <a href="{{ route('solutions.show', $solution) }}" class="text-decoration-none text-dark">
                        <div class="card h-100 shadow-sm">
                            @if($solution->image)
                                <img src="{{ Storage::url($solution->image) }}" class="card-img-top" style="height:180px; object-fit:cover;">
                            @endif
                            <div class="card-body">
                                <h5 class="card-title">{{ $solution->name }}</h5>
                                <p class="card-text text-muted small">{{ Str::limit($solution->short_description, 100) }}</p>
                            </div>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>
        <div class="mt-3">{{ $solutions->links() }}</div>
    @endif
</div>
@endsection
