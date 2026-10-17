@extends('admin.layouts.admin')
@section('title', 'Admin Users')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0">Admin Users</h3>
        <a href="{{ route('admin.users.create') }}" class="btn btn-primary">+ Add User</a>
    </div>

    @if(session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger">@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>@endif

    <form method="GET" class="row g-2 mb-3">
        <div class="col-md-5">
            <input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="Search name or email">
        </div>
        <div class="col-md-3">
            <select name="role" class="form-select">
                <option value="">All roles</option>
                @foreach(\App\Models\User::ROLES as $key => $label)
                    <option value="{{ $key }}" @selected(request('role') === $key)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-auto">
            <button class="btn btn-outline-secondary">Filter</button>
            @if(request()->hasAny(['q','role']))<a href="{{ route('admin.users.index') }}" class="btn btn-link">Clear</a>@endif
        </div>
    </form>

    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr><th>Name</th><th>Email</th><th>Role</th><th>Status</th><th>Last login</th><th class="text-end">Actions</th></tr>
                </thead>
                <tbody>
                    @forelse($users as $u)
                        <tr>
                            <td class="fw-semibold">
                                {{ $u->name }}
                                @if($u->id === auth()->id())<span class="badge bg-light text-dark border ms-1">You</span>@endif
                            </td>
                            <td>{{ $u->email }}</td>
                            <td><span class="badge bg-light text-dark border">{{ \App\Models\User::ROLES[$u->role] ?? $u->role }}</span></td>
                            <td><span class="badge {{ $u->status === 'active' ? 'bg-success' : 'bg-secondary' }}">{{ ucfirst($u->status) }}</span></td>
                            <td class="text-muted small">{{ $u->last_login_at ? \Illuminate\Support\Carbon::parse($u->last_login_at)->diffForHumans() : 'Never' }}</td>
                            <td class="text-end">
                                <a href="{{ route('admin.users.edit', $u) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                                @if($u->id !== auth()->id())
                                    <form action="{{ route('admin.users.destroy', $u) }}" method="POST" class="d-inline"
                                          onsubmit="return confirm('Delete {{ addslashes($u->name) }}? This cannot be undone.')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger">Delete</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-4">No users found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-3">{{ $users->links() }}</div>
@endsection