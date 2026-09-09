@extends('admin.layouts.admin')
@section('title', $banner->exists ? 'Edit Banner' : 'Add Banner')

@section('content')
    <h3 class="mb-4">{{ $banner->exists ? 'Edit Banner' : 'Add Banner' }}</h3>
    @if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif

    <form method="POST" action="{{ $banner->exists ? route('admin.banners.update', $banner) : route('admin.banners.store') }}" enctype="multipart/form-data">
        @csrf
        @if($banner->exists) @method('PUT') @endif

        <div class="card shadow-sm mb-3">
            <div class="card-body row g-3">
                <div class="col-md-6"><label class="form-label">Title</label><input type="text" name="title" value="{{ old('title', $banner->title) }}" class="form-control"></div>
                <div class="col-md-6"><label class="form-label">Subtitle</label><input type="text" name="subtitle" value="{{ old('subtitle', $banner->subtitle) }}" class="form-control"></div>
                <div class="col-md-6">
                    <label class="form-label">Image {{ $banner->exists ? '' : '*' }}</label>
                    <input type="file" name="image" class="form-control" accept="image/*" {{ $banner->exists ? '' : 'required' }}>
                    @if($banner->image)<img src="{{ Storage::url($banner->image) }}" class="mt-2 rounded" style="max-height:80px">@endif
                </div>
                <div class="col-md-6">
                    <label class="form-label">Position</label>
                    <select name="position" class="form-select">
                        <option value="home_hero" @selected(old('position', $banner->position ?? 'home_hero')==='home_hero')>Home Hero</option>
                        <option value="home_promo" @selected(old('position', $banner->position)==='home_promo')>Home Promo</option>
                        <option value="category_top" @selected(old('position', $banner->position)==='category_top')>Category Page Top</option>
                    </select>
                </div>
                <div class="col-md-6"><label class="form-label">Link URL</label><input type="text" name="link_url" value="{{ old('link_url', $banner->link_url) }}" class="form-control"></div>
                <div class="col-md-6"><label class="form-label">Button Text</label><input type="text" name="button_text" value="{{ old('button_text', $banner->button_text) }}" class="form-control"></div>
                <div class="col-md-6"><label class="form-label">Sort Order</label><input type="number" name="sort_order" value="{{ old('sort_order', $banner->sort_order ?? 0) }}" class="form-control" min="0"></div>
                <div class="col-md-6 d-flex align-items-end">
                    <div class="form-check">
                        <input type="checkbox" name="status" value="1" class="form-check-input" id="status" @checked(old('status', $banner->status ?? true))>
                        <label class="form-check-label" for="status">Active</label>
                    </div>
                </div>
            </div>
        </div>

        <button class="btn btn-primary">Save Banner</button>
        <a href="{{ route('admin.banners.index') }}" class="btn btn-outline-secondary">Cancel</a>
    </form>
@endsection
