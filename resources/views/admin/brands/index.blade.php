@extends('admin.layouts.admin')
@section('title', 'Brands')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0">Brands</h3>
        <a href="{{ route('admin.brands.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Add Brand</a>
    </div>

    @if(session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif

    <div class="card shadow-sm">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light">
                <tr><th>Logo</th><th>Name</th><th>Products</th><th>Status</th><th class="text-end">Actions</th></tr>
            </thead>
            <tbody>
                @foreach($brands as $brand)
                    <tr>
                        <td>@if($brand->logo)<img src="{{ Storage::url($brand->logo) }}" style="height:32px"> @else — @endif</td>
                        <td>{{ $brand->name }}</td>
                        <td>{{ $brand->products_count }}</td>
                        <td><span class="badge {{ $brand->status ? 'bg-success' : 'bg-secondary' }}">{{ $brand->status ? 'Active' : 'Inactive' }}</span></td>
                        <td class="text-end">
                            <a href="{{ route('admin.brands.edit', $brand) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                            <form action="{{ route('admin.brands.destroy', $brand) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this brand?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-3">{{ $brands->links() }}</div>
@endsection
