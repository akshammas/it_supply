@extends('admin.layouts.admin')
@section('title', $post->exists ? 'Edit Post' : 'Add Post')

@section('content')
    <h3 class="mb-4">{{ $post->exists ? 'Edit Post' : 'Add Post' }}</h3>

    @if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif

    <form method="POST" action="{{ $post->exists ? route('admin.blog.update', $post) : route('admin.blog.store') }}" enctype="multipart/form-data">
        @csrf
        @if($post->exists) @method('PUT') @endif

        <div class="card shadow-sm mb-3">
            <div class="card-body row g-3">
                <div class="col-md-8">
                    <label class="form-label">Title *</label>
                    <input type="text" name="title" value="{{ old('title', $post->title) }}" class="form-control" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Category</label>
                    <select name="blog_category_id" class="form-select">
                        <option value="">— None —</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" @selected(old('blog_category_id', $post->blog_category_id) == $cat->id)>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Slug</label>
                    <input type="text" name="slug" value="{{ old('slug', $post->slug) }}" class="form-control" placeholder="Auto-generated if blank">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Published Date</label>
                    <input type="date" name="published_at" value="{{ old('published_at', $post->published_at?->format('Y-m-d')) }}" class="form-control">
                </div>
                <div class="col-12">
                    <label class="form-label">Excerpt</label>
                    <textarea name="excerpt" rows="2" class="form-control">{{ old('excerpt', $post->excerpt) }}</textarea>
                </div>
                <div class="col-12">
                    <label class="form-label">Content</label>
                    <textarea name="content" rows="10" class="form-control">{{ old('content', $post->content) }}</textarea>
                    <div class="form-text">Plain text/HTML for now — a rich text editor (TinyMCE/Quill) can be added later if needed.</div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Featured Image</label>
                    <input type="file" name="featured_image" class="form-control" accept="image/*">
                    @if($post->featured_image)<img src="{{ Storage::url($post->featured_image) }}" class="mt-2 rounded" style="max-height:80px">@endif
                </div>
                <div class="col-md-6 d-flex align-items-end">
                    <div class="form-check">
                        <input type="checkbox" name="status" value="1" class="form-check-input" id="status" @checked(old('status', $post->status ?? false))>
                        <label class="form-check-label" for="status">Published</label>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm mb-3">
            <div class="card-header bg-white fw-semibold">SEO</div>
            <div class="card-body row g-3">
                <div class="col-md-6">
                    <label class="form-label">Meta Title</label>
                    <input type="text" name="meta_title" value="{{ old('meta_title', $post->meta_title) }}" class="form-control">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Meta Description</label>
                    <input type="text" name="meta_description" value="{{ old('meta_description', $post->meta_description) }}" class="form-control">
                </div>
            </div>
        </div>

        <button class="btn btn-primary">Save Post</button>
        <a href="{{ route('admin.blog.index') }}" class="btn btn-outline-secondary">Cancel</a>
    </form>
@endsection
