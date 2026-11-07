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

                @include('admin.partials.tick-list', [
                    'name' => 'category_ids',
                    'title' => 'Show on these categories (category page and its products)',
                    'items' => $categories->map(fn ($c) => ['id' => $c->id, 'label' => ($c->parent ? $c->parent->name.' › ' : '').$c->name])->all(),
                    'selected' => $faq->categories->pluck('id')->all(),
                    'help' => 'The FAQ appears on the ticked category pages and on the products inside them.',
                ])

                @include('admin.partials.tick-list', [
                    'name' => 'brand_ids',
                    'title' => 'Show on these brand pages',
                    'items' => $brands->map(fn ($b) => ['id' => $b->id, 'label' => $b->name])->all(),
                    'selected' => $faq->brands->pluck('id')->all(),
                    'help' => 'The FAQ appears on the ticked brand pages. If nothing is ticked anywhere, it is not shown.',
                ])
            </div>
        </div>

        <button class="btn btn-primary">Save FAQ</button>
        <a href="{{ route('admin.faqs.index') }}" class="btn btn-outline-secondary">Cancel</a>
    </form>
@endsection