@extends('admin.layouts.admin')
@section('title', $brand->exists ? 'Edit Brand' : 'Add Brand')

@section('content')
    <h3 class="mb-4">{{ $brand->exists ? 'Edit Brand' : 'Add Brand' }}</h3>

    @if($errors->any())
        <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
    @endif

    <form method="POST" action="{{ $brand->exists ? route('admin.brands.update', $brand) : route('admin.brands.store') }}" enctype="multipart/form-data">
        @csrf
        @if($brand->exists) @method('PUT') @endif

        <div class="card shadow-sm mb-3">
            <div class="card-body row g-3">
                <div class="col-md-6">
                    <label class="form-label">Name *</label>
                    <input type="text" name="name" value="{{ old('name', $brand->name) }}" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Slug</label>
                    <input type="text" name="slug" value="{{ old('slug', $brand->slug) }}" class="form-control" placeholder="Auto-generated if blank">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Website</label>
                    <input type="url" name="website" value="{{ old('website', $brand->website) }}" class="form-control" placeholder="https://">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Logo</label>
                    <input type="file" name="logo" class="form-control" accept="image/*">
                    @if($brand->logo)<img src="{{ Storage::url($brand->logo) }}" class="mt-2" style="max-height:60px">@endif
                </div>
                <div class="col-12">
                    <label class="form-label">Description</label>
                    <textarea name="description" rows="3" class="form-control">{{ old('description', $brand->description) }}</textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Sort Order</label>
                    <input type="number" name="sort_order" value="{{ old('sort_order', $brand->sort_order ?? 0) }}" class="form-control" min="0">
                </div>
                <div class="col-md-6 d-flex align-items-end">
                    <div class="form-check">
                        <input type="checkbox" name="status" value="1" class="form-check-input" id="status" @checked(old('status', $brand->status ?? true))>
                        <label class="form-check-label" for="status">Active</label>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm mb-3">
            <div class="card-header bg-white fw-semibold">Page content <span class="text-muted fw-normal small">(shown below the product cards)</span></div>
            <div class="card-body">
                <textarea name="seo_content" rows="14" class="form-control font-monospace" style="font-size:.85rem;"
                          placeholder="Buy Servers in UAE: HPE, Dell &amp; Lenovo&#10;Intro paragraph about the products...&#10;Why Choose Us?&#10;* Authorized Distributor: Genuine products with full warranty.&#10;* Fast Delivery: Quick shipping across the UAE.">{{ old('seo_content', $brand->seo_content) }}</textarea>
                <div class="form-text">
                    Paste plain text or HTML. In plain text, a short line with no full stop at the end becomes a heading, lines starting with <code>*</code> become bullets (the text before the first colon is made bold), and <code>[text](https://...)</code> makes a link. If the text contains HTML tags it is shown exactly as written. Leave empty to show nothing.
                    FAQs are linked from <a href="{{ route('admin.faqs.index') }}">FAQs</a> and blog posts from the
                    <a href="{{ route('admin.blog.index') }}">Blog</a> post form, by ticking this brand.
                </div>
            </div>
        </div>

        <div class="card shadow-sm mb-3">
            <div class="card-header bg-white fw-semibold">SEO</div>
            <div class="card-body row g-3">
                <div class="col-md-6">
                    <label class="form-label">Meta Title</label>
                    <input type="text" name="meta_title" value="{{ old('meta_title', $brand->meta_title) }}" class="form-control">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Meta Description</label>
                    <input type="text" name="meta_description" value="{{ old('meta_description', $brand->meta_description) }}" class="form-control">
                </div>
            </div>
        </div>

        <button class="btn btn-primary">Save Brand</button>
        <a href="{{ route('admin.brands.index') }}" class="btn btn-outline-secondary">Cancel</a>
    </form>
@endsection
