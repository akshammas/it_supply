@extends('admin.layouts.admin')
@section('title', $page->exists ? 'Edit Page' : 'Add Page')

@section('content')
    <h3 class="mb-4">{{ $page->exists ? 'Edit Page' : 'Add Page' }}</h3>
    @if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif

    <form method="POST" action="{{ $page->exists ? route('admin.pages.update', $page) : route('admin.pages.store') }}" enctype="multipart/form-data">
        @csrf
        @if($page->exists) @method('PUT') @endif

        <div class="card shadow-sm mb-3">
            <div class="card-body row g-3">
                <div class="col-md-6">
                    <label class="form-label">Title *</label>
                    <input type="text" name="title" value="{{ old('title', $page->title) }}" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Slug</label>
                    <input type="text" name="slug" value="{{ old('slug', $page->slug) }}" class="form-control" placeholder="e.g. about-us, privacy-policy">
                </div>
                <div class="col-12">
                    <label class="form-label">Content</label>
                    <textarea name="content" rows="10" class="form-control">{{ old('content', $page->content) }}</textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Featured Image</label>
                    <input type="file" name="featured_image" class="form-control" accept="image/*">
                    @if($page->featured_image)<img src="{{ Storage::url($page->featured_image) }}" class="mt-2 rounded" style="max-height:80px">@endif
                </div>
                <div class="col-md-6 d-flex align-items-end">
                    <div class="form-check">
                        <input type="checkbox" name="status" value="1" class="form-check-input" id="status" @checked(old('status', $page->status ?? true))>
                        <label class="form-check-label" for="status">Published</label>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm mb-3">
            <div class="card-header bg-white fw-semibold">SEO</div>
            <div class="card-body row g-3">
                <div class="col-md-6"><label class="form-label">Meta Title</label><input type="text" name="meta_title" value="{{ old('meta_title', $page->meta_title) }}" class="form-control"></div>
                <div class="col-md-6"><label class="form-label">Meta Description</label><input type="text" name="meta_description" value="{{ old('meta_description', $page->meta_description) }}" class="form-control"></div>
            </div>
        </div>

        <button class="btn btn-primary">Save Page</button>
        <a href="{{ route('admin.pages.index') }}" class="btn btn-outline-secondary">Cancel</a>
    </form>
@endsection
