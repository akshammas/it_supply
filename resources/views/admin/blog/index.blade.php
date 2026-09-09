@extends('admin.layouts.admin')
@section('title', 'Blog')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0">Blog</h3>
        <a href="{{ route('admin.blog.create') }}" class="btn btn-primary">+ Add Post</a>
    </div>

    @if(session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif

    <div class="card shadow-sm mb-3">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.blog-categories.store') }}" class="row g-2">
                @csrf
                <div class="col-md-9"><input type="text" name="name" class="form-control" placeholder="New blog category name" required></div>
                <div class="col-md-3"><button class="btn btn-outline-primary w-100">Add Category</button></div>
            </form>
            @if($categories->isNotEmpty())
                <div class="mt-2 d-flex flex-wrap gap-2">
                    @foreach($categories as $cat)
                        <span class="badge bg-light text-dark border">
                            {{ $cat->name }}
                            <form method="POST" action="{{ route('admin.blog-categories.destroy', $cat) }}" class="d-inline" onsubmit="return confirm('Delete category?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-link btn-sm p-0 text-danger">&times;</button>
                            </form>
                        </span>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <div class="card shadow-sm">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light"><tr><th>Title</th><th>Category</th><th>Status</th><th>Published</th><th class="text-end">Actions</th></tr></thead>
            <tbody>
                @foreach($posts as $post)
                    <tr>
                        <td>{{ $post->title }}</td>
                        <td>{{ $post->category?->name ?? '—' }}</td>
                        <td><span class="badge {{ $post->status ? 'bg-success' : 'bg-secondary' }}">{{ $post->status ? 'Published' : 'Draft' }}</span></td>
                        <td>{{ $post->published_at?->format('d M Y') ?? '—' }}</td>
                        <td class="text-end">
                            <a href="{{ route('admin.blog.edit', $post) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                            <form action="{{ route('admin.blog.destroy', $post) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this post?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-3">{{ $posts->links() }}</div>
@endsection
