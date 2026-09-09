@extends('admin.layouts.admin')
@section('title', 'Pages')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0">Pages</h3>
        <a href="{{ route('admin.pages.create') }}" class="btn btn-primary">+ Add Page</a>
    </div>
    @if(session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif

    <div class="card shadow-sm">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light"><tr><th>Title</th><th>Slug</th><th>Status</th><th class="text-end">Actions</th></tr></thead>
            <tbody>
                @foreach($pages as $page)
                    <tr>
                        <td>{{ $page->title }}</td>
                        <td class="text-muted">/{{ $page->slug }}</td>
                        <td><span class="badge {{ $page->status ? 'bg-success' : 'bg-secondary' }}">{{ $page->status ? 'Published' : 'Draft' }}</span></td>
                        <td class="text-end">
                            <a href="{{ route('admin.pages.edit', $page) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                            <form action="{{ route('admin.pages.destroy', $page) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this page?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-3">{{ $pages->links() }}</div>
@endsection
