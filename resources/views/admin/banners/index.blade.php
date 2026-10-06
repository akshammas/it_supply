@extends('admin.layouts.admin')
@section('title', 'Banners')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0">Banners</h3>
        <a href="{{ route('admin.banners.create') }}" class="btn btn-primary">+ Add Banner</a>
    </div>
    @if(session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif

    <div class="card shadow-sm">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light"><tr><th>Image</th><th>Title</th><th>Position</th><th>Categories</th><th>Status</th><th class="text-end">Actions</th></tr></thead>
            <tbody>
                @foreach($banners as $banner)
                    <tr>
                        <td><img src="{{ Storage::url($banner->image) }}" style="height:40px"></td>
                        <td>{{ $banner->title ?? '—' }}</td>
                        <td><span class="badge bg-light text-dark border">{{ $banner->position }}</span></td>
                                                <td>
                            @if($banner->position !== 'category_top')
                                —
                            @elseif($banner->categories->isEmpty())
                                <span class="text-muted">All categories</span>
                            @else
                                {{ $banner->categories->pluck('name')->take(3)->implode(', ') }}
                                @if($banner->categories->count() > 3)
                                    <span class="text-muted">+{{ $banner->categories->count() - 3 }} more</span>
                                @endif
                            @endif
                        </td>
                        <td><span class="badge {{ $banner->status ? 'bg-success' : 'bg-secondary' }}">{{ $banner->status ? 'Active' : 'Inactive' }}</span></td>
                        <td class="text-end">
                            <a href="{{ route('admin.banners.edit', $banner) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                            <form action="{{ route('admin.banners.destroy', $banner) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this banner?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-3">{{ $banners->links() }}</div>
@endsection
