@extends('admin.layouts.admin')
@section('title', 'Import Products')

@section('content')
    <h3 class="mb-4">Import Products from CSV</h3>

    @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif

    <div class="card shadow-sm mb-3">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.imports.preview') }}" enctype="multipart/form-data">
                @csrf
                <label class="form-label">CSV File</label>
                <input type="file" name="csv_file" class="form-control mb-3" accept=".csv,.txt" required>
                <button class="btn btn-primary">Upload & Preview</button>
                <a href="{{ route('admin.imports.index') }}" class="btn btn-outline-secondary">View Past Imports</a>
            </form>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-white fw-semibold">CSV Format Notes</div>
        <div class="card-body small text-muted">
            <ul class="mb-0">
                <li>First row must be column headers.</li>
                <li><strong>Brand</strong> and <strong>Category</strong> columns must match an existing brand/category name exactly (case-insensitive) — they won't be created automatically.</li>
                <li>If a row's <strong>SKU</strong> matches an existing product, that product is <strong>updated</strong> rather than duplicated.</li>
                <li><strong>Images</strong> column: comma-separated direct image URLs (e.g. <code>https://example.com/a.jpg,https://example.com/b.jpg</code>). The first one becomes the primary image.</li>
                <li>You'll map your CSV's actual column names to these fields on the next screen — they don't need to match exactly.</li>
            </ul>
        </div>
    </div>
@endsection
