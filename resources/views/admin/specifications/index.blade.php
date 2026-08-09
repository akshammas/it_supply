@extends('admin.layouts.admin')
@section('title', 'Specifications')

@section('content')
    <h3 class="mb-4">Specifications</h3>

    @if(session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-white fw-semibold">Add Specification Group</div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.specification-groups.store') }}" class="row g-2">
                @csrf
                <div class="col-md-8"><input type="text" name="name" class="form-control" placeholder="e.g. Server Specifications" required></div>
                <div class="col-md-2"><input type="number" name="sort_order" class="form-control" placeholder="Sort order" min="0"></div>
                <div class="col-md-2"><button class="btn btn-primary w-100">Add Group</button></div>
            </form>
        </div>
    </div>

    @foreach($groups as $group)
        <div class="card shadow-sm mb-3">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <span class="fw-semibold">{{ $group->name }}</span>
                <form method="POST" action="{{ route('admin.specification-groups.destroy', $group) }}" onsubmit="return confirm('Delete this group?')">
                    @csrf @method('DELETE')
                    <button class="btn btn-sm btn-outline-danger">Delete Group</button>
                </form>
            </div>
            <div class="card-body">
                <table class="table table-sm mb-3">
                    <thead><tr><th>Specification</th><th style="width:120px">Sort</th><th class="text-end">Actions</th></tr></thead>
                    <tbody>
                        @foreach($group->specifications as $spec)
                            <tr>
                                <td>{{ $spec->name }}</td>
                                <td>{{ $spec->sort_order }}</td>
                                <td class="text-end">
                                    <form method="POST" action="{{ route('admin.specifications.destroy', $spec) }}" class="d-inline" onsubmit="return confirm('Delete this specification?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <form method="POST" action="{{ route('admin.specifications.store') }}" class="row g-2">
                    @csrf
                    <input type="hidden" name="specification_group_id" value="{{ $group->id }}">
                    <div class="col-md-8"><input type="text" name="name" class="form-control" placeholder="e.g. Processor" required></div>
                    <div class="col-md-2"><input type="number" name="sort_order" class="form-control" placeholder="Sort" min="0"></div>
                    <div class="col-md-2"><button class="btn btn-outline-primary w-100">Add</button></div>
                </form>
            </div>
        </div>
    @endforeach
@endsection
