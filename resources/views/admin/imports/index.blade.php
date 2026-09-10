@extends('admin.layouts.admin')
@section('title', 'Import History')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0">Import History</h3>
        <a href="{{ route('admin.imports.create') }}" class="btn btn-primary">New Import</a>
    </div>

    <div class="card shadow-sm">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light">
                <tr><th>File</th><th>Rows</th><th>Imported</th><th>Updated</th><th>Failed</th><th>Status</th><th>Date</th><th></th></tr>
            </thead>
            <tbody>
                @foreach($imports as $import)
                    <tr>
                        <td>{{ $import->original_filename }}</td>
                        <td>{{ $import->total_rows }}</td>
                        <td class="text-success">{{ $import->imported_count }}</td>
                        <td class="text-primary">{{ $import->updated_count }}</td>
                        <td class="text-danger">{{ $import->failed_count }}</td>
                        <td><span class="badge {{ $import->status === 'completed' ? 'bg-success' : 'bg-secondary' }} text-capitalize">{{ $import->status }}</span></td>
                        <td>{{ $import->created_at->format('d M Y H:i') }}</td>
                        <td><a href="{{ route('admin.imports.show', $import) }}" class="btn btn-sm btn-outline-secondary">View</a></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-3">{{ $imports->links() }}</div>
@endsection
