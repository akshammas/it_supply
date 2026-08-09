@extends('admin.layouts.admin')
@section('title', $product->exists ? 'Edit Product' : 'Add Product')

@php
    $productSpecs = $product->exists ? $product->specifications->pluck('value', 'specification_id') : collect();
@endphp

@section('content')
    <h3 class="mb-4">{{ $product->exists ? 'Edit Product' : 'Add Product' }}</h3>

    @if($errors->any())
        <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
    @endif
    @if(session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif

    <form method="POST" action="{{ $product->exists ? route('admin.products.update', $product) : route('admin.products.store') }}" enctype="multipart/form-data">
        @csrf
        @if($product->exists) @method('PUT') @endif

        <ul class="nav nav-tabs mb-3">
            <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#tab-general">General</a></li>
            <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-specs">Specifications</a></li>
            <li class="nav-item"><a class="nav-link {{ !$product->exists ? 'disabled' : '' }}" data-bs-toggle="tab" href="#tab-images">Images</a></li>
            <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-seo">SEO</a></li>
        </ul>

        <div class="tab-content">

            {{-- GENERAL --}}
            <div class="tab-pane fade show active" id="tab-general">
                <div class="card shadow-sm">
                    <div class="card-body row g-3">
                        <div class="col-md-6"><label class="form-label">Product Name *</label>
                            <input type="text" name="name" value="{{ old('name', $product->name) }}" class="form-control" required></div>
                        <div class="col-md-6"><label class="form-label">Slug</label>
                            <input type="text" name="slug" value="{{ old('slug', $product->slug) }}" class="form-control" placeholder="Auto-generated if blank"></div>

                        <div class="col-md-4"><label class="form-label">SKU *</label>
                            <input type="text" name="sku" value="{{ old('sku', $product->sku) }}" class="form-control" required></div>
                        <div class="col-md-4"><label class="form-label">Model Number</label>
                            <input type="text" name="model_number" value="{{ old('model_number', $product->model_number) }}" class="form-control"></div>
                        <div class="col-md-4"><label class="form-label">Condition *</label>
                            <select name="condition" class="form-select">
                                @foreach(['new'=>'New','refurbished'=>'Refurbished','used'=>'Used'] as $val=>$label)
                                    <option value="{{ $val }}" @selected(old('condition',$product->condition ?? 'new')==$val)>{{ $label }}</option>
                                @endforeach
                            </select></div>

                        <div class="col-md-6"><label class="form-label">Brand</label>
                            <select name="brand_id" class="form-select">
                                <option value="">— Select —</option>
                                @foreach($brands as $brand)<option value="{{ $brand->id }}" @selected(old('brand_id',$product->brand_id)==$brand->id)>{{ $brand->name }}</option>@endforeach
                            </select></div>
                        <div class="col-md-6"><label class="form-label">Category</label>
                            <select name="category_id" class="form-select">
                                <option value="">— Select —</option>
                                @foreach($categories as $category)<option value="{{ $category->id }}" @selected(old('category_id',$product->category_id)==$category->id)>{{ $category->name }}</option>@endforeach
                            </select></div>

                        <div class="col-12"><label class="form-label">Short Description</label>
                            <textarea name="short_description" rows="2" class="form-control">{{ old('short_description', $product->short_description) }}</textarea></div>
                        <div class="col-12"><label class="form-label">Full Description</label>
                            <textarea name="description" rows="6" class="form-control">{{ old('description', $product->description) }}</textarea></div>

                        <div class="col-md-3"><label class="form-label">Price Type *</label>
                            <select name="price_type" class="form-select" id="price_type">
                                @foreach(['fixed'=>'Fixed','sale'=>'Sale','on_request'=>'Price on Request'] as $val=>$label)
                                    <option value="{{ $val }}" @selected(old('price_type',$product->price_type ?? 'on_request')==$val)>{{ $label }}</option>
                                @endforeach
                            </select></div>
                        <div class="col-md-3"><label class="form-label">Price (AED)</label>
                            <input type="number" step="0.01" name="price" value="{{ old('price', $product->price) }}" class="form-control"></div>
                        <div class="col-md-3"><label class="form-label">Sale Price (AED)</label>
                            <input type="number" step="0.01" name="sale_price" value="{{ old('sale_price', $product->sale_price) }}" class="form-control"></div>
                        <div class="col-md-3"><label class="form-label">Stock Qty</label>
                            <input type="number" name="stock" value="{{ old('stock', $product->stock ?? 0) }}" class="form-control"></div>

                        <div class="col-md-4"><label class="form-label">Stock Status *</label>
                            <select name="stock_status" class="form-select">
                                @foreach(['in_stock'=>'In Stock','out_of_stock'=>'Out of Stock','preorder'=>'Pre-order'] as $val=>$label)
                                    <option value="{{ $val }}" @selected(old('stock_status',$product->stock_status ?? 'in_stock')==$val)>{{ $label }}</option>
                                @endforeach
                            </select></div>
                        <div class="col-md-4 d-flex align-items-end">
                            <div class="form-check"><input type="checkbox" name="featured" value="1" class="form-check-input" id="featured" @checked(old('featured',$product->featured))>
                                <label class="form-check-label" for="featured">Featured Product</label></div>
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <div class="form-check"><input type="checkbox" name="status" value="1" class="form-check-input" id="status" @checked(old('status',$product->status ?? true))>
                                <label class="form-check-label" for="status">Active</label></div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- SPECIFICATIONS --}}
            <div class="tab-pane fade" id="tab-specs">
                <div class="card shadow-sm">
                    <div class="card-body">
                        @forelse($specGroups as $group)
                            <h6 class="fw-semibold mt-2">{{ $group->name }}</h6>
                            <div class="row g-3 mb-3">
                                @foreach($group->specifications as $spec)
                                    <div class="col-md-6">
                                        <label class="form-label">{{ $spec->name }}</label>
                                        <input type="text" name="specs[{{ $spec->id }}]" value="{{ old('specs.'.$spec->id, $productSpecs[$spec->id] ?? '') }}" class="form-control">
                                    </div>
                                @endforeach
                            </div>
                        @empty
                            <p class="text-muted">No specification groups yet. Add some under <a href="{{ route('admin.specifications.index') }}">Specifications</a>.</p>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- IMAGES --}}
            <div class="tab-pane fade" id="tab-images">
                <div class="card shadow-sm">
                    <div class="card-body">
                        @if($product->exists)
                            <div class="row g-3 mb-3" id="product-images-grid">
                                @foreach($product->images as $image)
                                    <div class="col-md-2 col-4 text-center" data-image-id="{{ $image->id }}">
                                        <img src="{{ Storage::url($image->image) }}" class="img-fluid rounded border {{ $image->is_primary ? 'border-primary border-3' : '' }}">
                                        <div class="small mt-1">
                                            @if($image->is_primary)
                                                <span class="badge bg-primary">Primary</span>
                                            @else
                                                <button type="button" class="btn btn-link btn-sm p-0" onclick="setPrimaryImage({{ $product->id }}, {{ $image->id }})">Set primary</button>
                                            @endif
                                            <button type="button" class="btn btn-link btn-sm p-0 text-danger" onclick="deleteProductImage({{ $product->id }}, {{ $image->id }})">Remove</button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        <label class="form-label">Upload Images (up to 10, JPG/PNG/WebP)</label>
                        <input type="file" name="images[]" class="form-control" accept="image/*" multiple>
                        <div class="form-text">First uploaded image becomes primary automatically if the product has no images yet.</div>
                    </div>
                </div>
            </div>

            {{-- SEO --}}
            <div class="tab-pane fade" id="tab-seo">
                <div class="card shadow-sm">
                    <div class="card-body row g-3">
                        <div class="col-md-6"><label class="form-label">Meta Title</label>
                            <input type="text" name="meta_title" value="{{ old('meta_title', $product->meta_title) }}" class="form-control"></div>
                        <div class="col-md-6"><label class="form-label">Meta Description</label>
                            <input type="text" name="meta_description" value="{{ old('meta_description', $product->meta_description) }}" class="form-control"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-3">
            <button class="btn btn-primary">Save Product</button>
            <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary">Cancel</a>
        </div>
    </form>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/js/bootstrap.bundle.min.js"></script>
    <script>
        // Image actions can't be nested <form> elements inside the main
        // product edit form (browsers don't support nested forms and will
        // silently merge/mis-submit them). Use fetch() instead.
        function csrfToken() {
            return document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        }

        function deleteProductImage(productId, imageId) {
            if (!confirm('Remove this image?')) return;

            fetch(`/admin/products/${productId}/images/${imageId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': csrfToken(),
                    'Accept': 'application/json',
                },
            }).then(res => {
                if (res.ok) {
                    window.location.reload();
                } else {
                    alert('Could not remove image. Please try again.');
                }
            });
        }

        function setPrimaryImage(productId, imageId) {
            fetch(`/admin/products/${productId}/images/${imageId}/primary`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken(),
                    'Accept': 'application/json',
                },
            }).then(res => {
                if (res.ok) {
                    window.location.reload();
                } else {
                    alert('Could not update primary image. Please try again.');
                }
            });
        }
    </script>
@endsection