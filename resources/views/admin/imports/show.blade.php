@extends('admin.layouts.admin')
@section('title', 'Import #'.$import->id)

@section('content')
    <h3 class="mb-4">Import: {{ $import->original_filename }}</h3>

    @if(session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif

    <div class="card shadow-sm mb-3">
        <div class="card-body">
            <div class="d-flex justify-content-between mb-2">
                <span class="fw-semibold text-capitalize">{{ $import->status }}</span>
                <span>{{ $import->processed_rows }} / {{ $import->total_rows }} rows</span>
            </div>
            <div class="progress" style="height:20px;">
                <div class="progress-bar {{ $import->status === 'completed' ? 'bg-success' : 'progress-bar-striped progress-bar-animated' }}"
                     style="width: {{ $import->progressPercent() }}%">{{ $import->progressPercent() }}%</div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-md-4">
            <div class="card text-center shadow-sm"><div class="card-body">
                <div class="fs-3 fw-bold text-success">{{ $import->imported_count }}</div>
                <div class="text-muted small">Imported (new)</div>
            </div></div>
        </div>
        <div class="col-md-4">
            <div class="card text-center shadow-sm"><div class="card-body">
                <div class="fs-3 fw-bold text-primary">{{ $import->updated_count }}</div>
                <div class="text-muted small">Updated (existing SKU)</div>
            </div></div>
        </div>
        <div class="col-md-4">
            <div class="card text-center shadow-sm border-danger"><div class="card-body">
                <div class="fs-3 fw-bold text-danger">{{ $import->failed_count }}</div>
                <div class="text-muted small">Failed</div>
            </div></div>
        </div>
    </div>

    @if(!empty($import->errors))
        <div class="card shadow-sm mb-3">
            <div class="card-header bg-white fw-semibold d-flex justify-content-between align-items-center">
                Errors
                <a href="{{ route('admin.imports.errors', $import) }}" class="btn btn-sm btn-outline-danger">Download Error CSV</a>
            </div>
            <table class="table table-sm mb-0">
                <thead class="table-light"><tr><th style="width:80px">Row</th><th>Error</th></tr></thead>
                <tbody>
                    @foreach(array_slice($import->errors, 0, 20) as $error)
                        <tr><td>{{ $error['row'] }}</td><td>{{ $error['message'] }}</td></tr>
                    @endforeach
                </tbody>
            </table>
            @if(count($import->errors) > 20)
                <div class="card-footer text-muted small">Showing first 20 of {{ count($import->errors) }} errors — download the CSV for the full list.</div>
            @endif
        </div>
    @endif

    <a href="{{ route('admin.products.index') }}" class="btn btn-primary">View Products</a>
    <a href="{{ route('admin.imports.create') }}" class="btn btn-outline-secondary">New Import</a>

    @if($import->status !== 'completed')
        <script>
            // Import processes asynchronously via the queue — poll every
            // 3s until it's done, since there's nothing to show live
            // otherwise (section 27's "Import Summary" needs fresh data).
            setTimeout(() => window.location.reload(), 3000);
        </script>
    @endif
@endsection
