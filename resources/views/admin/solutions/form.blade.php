@extends('admin.layouts.admin')
@section('title', $solution->exists ? 'Edit Solution' : 'Add Solution')

@section('content')
    <h3 class="mb-4">{{ $solution->exists ? 'Edit Solution' : 'Add Solution' }}</h3>

    @if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif

    <form method="POST" action="{{ $solution->exists ? route('admin.solutions.update', $solution) : route('admin.solutions.store') }}" enctype="multipart/form-data">
        @csrf
        @if($solution->exists) @method('PUT') @endif

        <div class="card shadow-sm mb-3">
            <div class="card-body row g-3">
                <div class="col-md-6">
                    <label class="form-label">Name *</label>
                    <input type="text" name="name" value="{{ old('name', $solution->name) }}" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Slug</label>
                    <input type="text" name="slug" value="{{ old('slug', $solution->slug) }}" class="form-control" placeholder="Auto-generated if blank">
                </div>
                <div class="col-12">
                    <label class="form-label">Short Description</label>
                    <textarea name="short_description" rows="2" class="form-control">{{ old('short_description', $solution->short_description) }}</textarea>
                </div>
                <div class="col-12">
                    <label class="form-label">Full Description</label>
                    <textarea name="description" rows="5" class="form-control">{{ old('description', $solution->description) }}</textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Image</label>
                    <input type="file" name="image" class="form-control" accept="image/*">
                    @if($solution->image)<img src="{{ Storage::url($solution->image) }}" class="mt-2 rounded" style="max-height:80px">@endif
                </div>
                <div class="col-md-6 d-flex align-items-end">
                    <div class="form-check">
                        <input type="checkbox" name="status" value="1" class="form-check-input" id="status" @checked(old('status', $solution->status ?? true))>
                        <label class="form-check-label" for="status">Active</label>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm mb-3">
            <div class="card-header bg-white fw-semibold">Related Products</div>
            <div class="card-body" style="max-height:300px; overflow-y:auto;">
                @foreach($products as $product)
                    <div class="form-check">
                        <input type="checkbox" name="product_ids[]" value="{{ $product->id }}" class="form-check-input" id="product{{ $product->id }}"
                               @checked(in_array($product->id, $selectedProductIds ?? []))>
                        <label class="form-check-label" for="product{{ $product->id }}">{{ $product->name }}</label>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="card shadow-sm mb-3">
            <div class="card-header bg-white fw-semibold">SEO</div>
            <div class="card-body row g-3">
                <div class="col-md-6">
                    <label class="form-label">Meta Title</label>
                    <input type="text" name="meta_title" value="{{ old('meta_title', $solution->meta_title) }}" class="form-control">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Meta Description</label>
                    <input type="text" name="meta_description" value="{{ old('meta_description', $solution->meta_description) }}" class="form-control">
                </div>
            </div>
        </div>

        <button class="btn btn-primary">Save Solution</button>
        <a href="{{ route('admin.solutions.index') }}" class="btn btn-outline-secondary">Cancel</a>
    </form>
@endsection
