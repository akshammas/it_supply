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
                        <option value="brand_top" @selected(old('position', $banner->position)==='brand_top')>Brand Page Top</option>
                    </select>
                </div>
                                @php
                    $selectedCategories = collect(old('category_ids', $banner->exists ? $banner->categories->pluck('id')->all() : []))
                        ->map(fn ($id) => (int) $id)->all();
                @endphp
                {{-- Category picker (only for Category Page Top) --}}
                <div class="col-12" id="categoryPicker" style="display:none">
                    <label class="form-label fw-semibold">Show on these categories</label>
                    <div class="border rounded p-2">
                        <div class="form-check border-bottom pb-2 mb-2">
                            <input type="checkbox" class="form-check-input" id="catAll" data-picker-all="category">
                            <label class="form-check-label fw-semibold" for="catAll">Select all</label>
                        </div>
                        <div class="row" style="max-height:220px; overflow:auto;">
                            @php $selCats = old('category_ids', $banner->categories->pluck('id')->all()); @endphp
                            @foreach($categories as $cat)
                                <div class="col-md-4 col-sm-6">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" name="category_ids[]" value="{{ $cat->id }}"
                                            id="cat{{ $cat->id }}" data-picker-item="category" @checked(in_array($cat->id, $selCats))>
                                        <label class="form-check-label" for="cat{{ $cat->id }}">
                                            {{ $cat->parent ? $cat->parent->name.' › ' : '' }}{{ $cat->name }}
                                        </label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="form-text">Leave everything unticked to show this banner on every category page.</div>
                </div>

                {{-- Brand picker (only for Brand Page Top) --}}
                <div class="col-12" id="brandPicker" style="display:none">
                    <label class="form-label fw-semibold">Show on these brands</label>
                    <div class="border rounded p-2">
                        <div class="form-check border-bottom pb-2 mb-2">
                            <input type="checkbox" class="form-check-input" id="brandAll" data-picker-all="brand">
                            <label class="form-check-label fw-semibold" for="brandAll">Select all</label>
                        </div>
                        <div class="row" style="max-height:220px; overflow:auto;">
                            @php $selBrands = old('brand_ids', $banner->brands->pluck('id')->all()); @endphp
                            @foreach($brands as $brand)
                                <div class="col-md-4 col-sm-6">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" name="brand_ids[]" value="{{ $brand->id }}"
                                            id="brand{{ $brand->id }}" data-picker-item="brand" @checked(in_array($brand->id, $selBrands))>
                                        <label class="form-check-label" for="brand{{ $brand->id }}">{{ $brand->name }}</label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="form-text">The banner shows only on the brands you tick. At least one is required.</div>
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
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const position = document.querySelector('select[name=position]');
            const pickers  = { category_top: 'categoryPicker', brand_top: 'brandPicker' };

            function togglePickers() {
                Object.entries(pickers).forEach(([pos, id]) => {
                    document.getElementById(id).style.display = position.value === pos ? '' : 'none';
                });
            }

            ['category', 'brand'].forEach(type => {
                const all   = document.querySelector('[data-picker-all="' + type + '"]');
                const items = document.querySelectorAll('[data-picker-item="' + type + '"]');
                const sync  = () => { all.checked = items.length > 0 && [...items].every(i => i.checked); };
                all.addEventListener('change', () => { items.forEach(i => i.checked = all.checked); });
                items.forEach(i => i.addEventListener('change', sync));
                sync();
            });

            position.addEventListener('change', togglePickers);
            togglePickers();
        });
    </script>
@endsection
