@extends('admin.layouts.admin')
@section('title', 'FAQs')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <h3 class="mb-0">FAQs</h3>
        <div class="d-flex gap-2">
            <form method="GET" class="d-flex gap-2">
                <select name="category_id" class="form-select" onchange="this.form.submit()" style="min-width:220px">
                    <option value="">All categories</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" @selected(request('category_id') == $cat->id)>
                            {{ $cat->parent ? $cat->parent->name.' › ' : '' }}{{ $cat->name }}
                        </option>
                    @endforeach
                </select>
            </form>
            <a href="{{ route('admin.faqs.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Add FAQ</a>
        </div>
    </div>

    <div class="card shadow-sm">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light">
                <tr><th>Question</th><th>Shown on categories</th><th>Order</th><th>Status</th><th class="text-end">Actions</th></tr>
            </thead>
            <tbody>
                @forelse($faqs as $faq)
                    <tr>
                        <td style="max-width:380px">
                            <div class="fw-semibold">{{ $faq->question }}</div>
                            <div class="text-muted small">{{ \Illuminate\Support\Str::limit($faq->answer, 80) }}</div>
                        </td>
                        <td class="small">
                            @if($faq->categories->isEmpty())
                                <span class="text-danger">None selected (not shown)</span>
                            @else
                                {{ $faq->categories->take(3)->pluck('name')->implode(', ') }}
                                @if($faq->categories->count() > 3) <span class="text-muted">+{{ $faq->categories->count() - 3 }} more</span> @endif
                            @endif
                        </td>
                        <td>{{ $faq->sort_order }}</td>
                        <td><span class="badge {{ $faq->status ? 'bg-success' : 'bg-secondary' }}">{{ $faq->status ? 'Active' : 'Inactive' }}</span></td>
                        <td class="text-end">
                            <a href="{{ route('admin.faqs.edit', $faq) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                            <form action="{{ route('admin.faqs.destroy', $faq) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this FAQ?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted py-4">No FAQs yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-3">{{ $faqs->links() }}</div>
@endsection