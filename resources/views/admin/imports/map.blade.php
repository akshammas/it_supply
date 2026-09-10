@extends('admin.layouts.admin')
@section('title', 'Map Columns')

@section('content')
    <h3 class="mb-4">Map CSV Columns</h3>
    <p class="text-muted">Match each system field to the corresponding column in your CSV. Leave a field unmapped to skip it.</p>

    <form method="POST" action="{{ route('admin.imports.store') }}">
        @csrf
        <input type="hidden" name="path" value="{{ $path }}">
        <input type="hidden" name="original_filename" value="{{ $originalFilename }}">

        <div class="card shadow-sm mb-3">
            <div class="card-body row g-3">
                @foreach($systemFields as $field => $label)
                    <div class="col-md-6">
                        <label class="form-label">{{ $label }}</label>
                        <select name="mapping[{{ $field }}]" class="form-select">
                            <option value="">— Skip —</option>
                            @foreach($headers as $header)
                                <option value="{{ $header }}" @selected(strcasecmp($header, $field) === 0 || strcasecmp($header, str_replace('_', ' ', $field)) === 0)>
                                    {{ $header }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="card shadow-sm mb-3">
            <div class="card-header bg-white fw-semibold">Preview (first {{ count($sampleRows) }} rows)</div>
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead class="table-light">
                        <tr>@foreach($headers as $header)<th>{{ $header }}</th>@endforeach</tr>
                    </thead>
                    <tbody>
                        @foreach($sampleRows as $row)
                            <tr>@foreach($row as $cell)<td class="small">{{ Str::limit($cell, 40) }}</td>@endforeach</tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <button class="btn btn-primary btn-lg">Start Import</button>
        <a href="{{ route('admin.imports.create') }}" class="btn btn-outline-secondary">Cancel</a>
    </form>
@endsection
