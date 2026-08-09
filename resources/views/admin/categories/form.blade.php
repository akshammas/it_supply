@extends('admin.layouts.admin')
@section('title', $category->exists ? 'Edit Category' : 'Add Category')

@section('content')
    <h3 class="mb-4">{{ $category->exists ? 'Edit Category' : 'Add Category' }}</h3>

    @if($errors->any())
        <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
    @endif

    <form method="POST" action="{{ $category->exists ? route('admin.categories.update', $category) : route('admin.categories.store') }}" enctype="multipart/form-data">
        @csrf
        @if($category->exists) @method('PUT') @endif

        <div class="card shadow-sm mb-3">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Name *</label>
                        <input type="text" name="name" value="{{ old('name', $category->name) }}" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Slug</label>
                        <input type="text" name="slug" value="{{ old('slug', $category->slug) }}" class="form-control" placeholder="Auto-generated from name if left blank">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Parent Category</label>
                        <select name="parent_id" class="form-select">
                            <option value="">— None (top level) —</option>
                            @foreach($parents as $parent)
                                <option value="{{ $parent->id }}" @selected(old('parent_id', $category->parent_id) == $parent->id)>{{ $parent->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Sort Order</label>
                        <input type="number" name="sort_order" value="{{ old('sort_order', $category->sort_order ?? 0) }}" class="form-control" min="0">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Description</label>
                        <textarea name="description" rows="3" class="form-control">{{ old('description', $category->description) }}</textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Image</label>
                        <input type="file" name="image" class="form-control" accept="image/*">
                        @if($category->image)
                            <img src="{{ Storage::url($category->image) }}" class="mt-2 rounded" style="max-height:80px">
                        @endif
                    </div>
                    <div class="col-md-6 d-flex align-items-end">
                        <div class="form-check">
                            <input type="checkbox" name="status" value="1" class="form-check-input" id="status" @checked(old('status', $category->status ?? true))>
                            <label class="form-check-label" for="status">Active</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm mb-3">
            <div class="card-header bg-white fw-semibold">SEO</div>
            <div class="card-body row g-3">
                <div class="col-md-6">
                    <label class="form-label">Meta Title</label>
                    <input type="text" name="meta_title" value="{{ old('meta_title', $category->meta_title) }}" class="form-control">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Meta Description</label>
                    <input type="text" name="meta_description" value="{{ old('meta_description', $category->meta_description) }}" class="form-control">
                </div>
            </div>
        </div>

        <button class="btn btn-primary">Save Category</button>
        <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-secondary">Cancel</a>
    </form>
@endsection
