@extends('admin.layouts.admin')
@section('title', $faq->exists ? 'Edit FAQ' : 'Add FAQ')

@section('content')
    <h3 class="mb-4">{{ $faq->exists ? 'Edit FAQ' : 'Add FAQ' }}</h3>
    @if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif

    <form method="POST" action="{{ $faq->exists ? route('admin.faqs.update', $faq) : route('admin.faqs.store') }}">
        @csrf
        @if($faq->exists) @method('PUT') @endif

        <div class="card shadow-sm mb-3">
            <div class="card-body row g-3">
                <div class="col-12">
                    <label class="form-label">Question *</label>
                    <input type="text" name="question" value="{{ old('question', $faq->question) }}" class="form-control" required>
                </div>
                <div class="col-12">
                    <label class="form-label">Answer *</label>
                    <textarea name="answer" rows="5" class="form-control" required>{{ old('answer', $faq->answer) }}</textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Sort Order</label>
                    <input type="number" name="sort_order" min="0" value="{{ old('sort_order', $faq->sort_order ?? 0) }}" class="form-control">
                    <div class="form-text">Lower numbers appear first.</div>
                </div>
                <div class="col-md-6 d-flex align-items-end">
                    <div class="form-check">
                        <input type="checkbox" name="status" value="1" class="form-check-input" id="status" @checked(old('status', $faq->status ?? true))>
                        <label class="form-check-label" for="status">Active</label>
                    </div>
                </div>

                <div class="col-12">
                    <label class="form-label fw-semibold">Show on products in these categories</label>
                    <div class="border rounded p-2">
                        <div class="form-check border-bottom pb-2 mb-2">
                            <input type="checkbox" class="form-check-input" id="catAll">
                            <label class="form-check-label fw-semibold" for="catAll">Select all</label>
                        </div>
                        <div class="row" style="max-height:260px; overflow:auto;">
                            @php $selected = old('category_ids', $faq->categories->pluck('id')->all()); @endphp
                            @foreach($categories as $cat)
                                <div class="col-md-4 col-sm-6">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input cat-item" name="category_ids[]"
                                               value="{{ $cat->id }}" id="cat{{ $cat->id }}" @checked(in_array($cat->id, $selected))>
                                        <label class="form-check-label" for="cat{{ $cat->id }}">
                                            {{ $cat->parent ? $cat->parent->name.' › ' : '' }}{{ $cat->name }}
                                        </label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="form-text">The FAQ appears only on products in the ticked categories. If none are ticked, it is not shown anywhere.</div>
                </div>
            </div>
        </div>

        <button class="btn btn-primary">Save FAQ</button>
        <a href="{{ route('admin.faqs.index') }}" class="btn btn-outline-secondary">Cancel</a>
    </form>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const all = document.getElementById('catAll');
            const items = document.querySelectorAll('.cat-item');
            const sync = () => { all.checked = items.length > 0 && [...items].every(i => i.checked); };
            all.addEventListener('change', () => items.forEach(i => i.checked = all.checked));
            items.forEach(i => i.addEventListener('change', sync));
            sync();
        });
    </script>
@endsection