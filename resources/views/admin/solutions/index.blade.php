@extends('admin.layouts.admin')
@section('title', 'Solutions')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0">Solutions</h3>
        <a href="{{ route('admin.solutions.create') }}" class="btn btn-primary">+ Add Solution</a>
    </div>

    @if(session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif

    <div class="card shadow-sm">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light"><tr><th>Name</th><th>Products</th><th>Status</th><th class="text-end">Actions</th></tr></thead>
            <tbody>
                @foreach($solutions as $solution)
                    <tr>
                        <td>{{ $solution->name }}</td>
                        <td>{{ $solution->products_count }}</td>
                        <td><span class="badge {{ $solution->status ? 'bg-success' : 'bg-secondary' }}">{{ $solution->status ? 'Active' : 'Inactive' }}</span></td>
                        <td class="text-end">
                            <a href="{{ route('admin.solutions.edit', $solution) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                            <form action="{{ route('admin.solutions.destroy', $solution) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this solution?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-3">{{ $solutions->links() }}</div>
@endsection
