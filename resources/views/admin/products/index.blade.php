@extends('admin.layouts.admin')
@section('title', 'Products')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0">Products</h3>
        <div>
            <a href="{{ route('admin.products.export') }}" class="btn btn-outline-secondary"><i class="bi bi-download me-1"></i>Export CSV</a>
            <a href="{{ route('admin.products.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Add Product</a>
        </div>
    </div>

    @if(session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif

    <form method="GET" class="card shadow-sm mb-3">
        <div class="card-body row g-2">
            <div class="col-md-4"><input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Search name, SKU, model..."></div>
            <div class="col-md-3">
                <select name="brand_id" class="form-select">
                    <option value="">All Brands</option>
                    @foreach($brands as $brand)<option value="{{ $brand->id }}" @selected(request('brand_id') == $brand->id)>{{ $brand->name }}</option>@endforeach
                </select>
            </div>
            <div class="col-md-3">
                <select name="category_id" class="form-select">
                    <option value="">All Categories</option>
                    @foreach($categories as $category)<option value="{{ $category->id }}" @selected(request('category_id') == $category->id)>{{ $category->name }}</option>@endforeach
                </select>
            </div>
            <div class="col-md-2"><button class="btn btn-outline-primary w-100">Filter</button></div>
        </div>
    </form>

    <form method="POST" action="{{ route('admin.products.bulk') }}" id="bulk-form">
        @csrf
        <div class="card shadow-sm mb-2">
            <div class="card-body d-flex gap-2 align-items-center">
                <select name="action" class="form-select w-auto" required>
                    <option value="">Bulk action…</option>
                    <option value="activate">Activate</option>
                    <option value="deactivate">Deactivate</option>
                    <option value="feature">Mark Featured</option>
                    <option value="unfeature">Unmark Featured</option>
                    <option value="delete">Delete</option>
                </select>
                <button class="btn btn-outline-dark" onclick="return confirm('Apply this action to selected products?')">Apply</button>
                <span class="text-muted small ms-auto">Select products below, then choose an action.</span>
            </div>
        </div>

        <div class="card shadow-sm">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th style="width:30px"><input type="checkbox" onclick="document.querySelectorAll('.row-check').forEach(c=>c.checked=this.checked)"></th>
                        <th>Product</th><th>Brand</th><th>Category</th><th>Price</th><th>Stock</th><th>Status</th><th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($products as $product)
                        <tr>
                            <td><input type="checkbox" name="ids[]" value="{{ $product->id }}" class="row-check" form="bulk-form"></td>
                            <td>{{ $product->name }}<div class="text-muted small">{{ $product->sku }}</div></td>
                            <td>{{ $product->brand?->name ?? '—' }}</td>
                            <td>{{ $product->category?->name ?? '—' }}</td>
                            <td>
                                @if($product->price_type === 'on_request') <span class="text-muted">On Request</span>
                                @else AED {{ number_format($product->sale_price ?? $product->price, 2) }} @endif
                            </td>
                            <td>{{ $product->stock }}</td>
                            <td><span class="badge {{ $product->status ? 'bg-success' : 'bg-secondary' }}">{{ $product->status ? 'Active' : 'Inactive' }}</span></td>
                            <td class="text-end">
                                <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                                <form action="{{ route('admin.products.duplicate', $product) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button class="btn btn-sm btn-outline-primary">Duplicate</button>
                                </form>
                                <form action="{{ route('admin.products.destroy', $product) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this product?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </form>
    <div class="mt-3">{{ $products->links() }}</div>
@endsection
