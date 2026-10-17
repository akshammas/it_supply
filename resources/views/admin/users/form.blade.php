@extends('admin.layouts.admin')
@php($isEdit = $user->exists)
@php($isSelf = $isEdit && $user->id === auth()->id())
@section('title', $isEdit ? 'Edit User' : 'Add User')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0">{{ $isEdit ? 'Edit User' : 'Add User' }}</h3>
        <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">Back</a>
    </div>

    @if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif

    <form method="POST" action="{{ $isEdit ? route('admin.users.update', $user) : route('admin.users.store') }}">
        @csrf
        @if($isEdit) @method('PUT') @endif

        <div class="card shadow-sm mb-3" style="max-width:760px">
            <div class="card-header bg-white fw-semibold">Account</div>
            <div class="card-body row g-3">
                <div class="col-md-6">
                    <label class="form-label">Full name <span class="text-danger">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Email <span class="text-danger">*</span></label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Role <span class="text-danger">*</span></label>
                    <select name="role" class="form-select" @disabled($isSelf)>
                        @foreach(\App\Models\User::ROLES as $key => $label)
                            <option value="{{ $key }}" @selected(old('role', $user->role ?? 'product_manager') === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @if($isSelf)<div class="form-text">You can't change your own role.</div>@endif
                </div>
                <div class="col-md-6 d-flex align-items-end">
                    <div class="form-check form-switch mb-2">
                        <input class="form-check-input" type="checkbox" role="switch" name="status" value="1" id="status"
                               @checked(old('status', $isEdit ? ($user->status === 'active') : true)) @disabled($isSelf)>
                        <label class="form-check-label" for="status">Active (can sign in)</label>
                        @if($isSelf)<div class="form-text">You can't deactivate your own account.</div>@endif
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm mb-3" style="max-width:760px">
            <div class="card-header bg-white fw-semibold">{{ $isEdit ? 'Change password' : 'Password' }}</div>
            <div class="card-body row g-3">
                <div class="col-md-6">
                    <label class="form-label">Password @unless($isEdit)<span class="text-danger">*</span>@endunless</label>
                    <input type="password" name="password" class="form-control" autocomplete="new-password" @unless($isEdit) required @endunless>
                    <div class="form-text">{{ $isEdit ? 'Leave blank to keep the current password.' : 'Minimum 8 characters.' }}</div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Confirm password</label>
                    <input type="password" name="password_confirmation" class="form-control" autocomplete="new-password">
                </div>
            </div>
        </div>

        <button class="btn btn-primary">{{ $isEdit ? 'Save changes' : 'Create user' }}</button>
    </form>
@endsection