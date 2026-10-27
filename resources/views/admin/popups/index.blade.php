@extends('admin.layouts.admin')
@section('title', 'Popups')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0">Popups</h3>
        <a href="{{ route('admin.popups.create') }}" class="btn btn-primary">+ Add Popup</a>
    </div>
    @if(session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif

    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr><th>Popup</th><th>Shows on</th><th>When</th><th>How often</th><th>Schedule</th><th>Status</th><th class="text-end">Actions</th></tr>
                </thead>
                <tbody>
                    @forelse($popups as $popup)
                        @php
                            $badge = ['live' => 'bg-success', 'scheduled' => 'bg-info text-dark', 'expired' => 'bg-secondary', 'off' => 'bg-secondary'][$popup->state];
                        @endphp
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    @if($popup->image)
                                        <img src="{{ Storage::url($popup->image) }}" alt="" style="height:40px;width:56px;object-fit:cover;border-radius:6px">
                                    @endif
                                    <div>
                                        <div class="fw-semibold">{{ $popup->name }}</div>
                                        <div class="text-muted small">{{ \App\Models\Popup::LAYOUTS[$popup->layout] ? Str::before(\App\Models\Popup::LAYOUTS[$popup->layout], ' (') : $popup->layout }} &middot; Priority {{ $popup->sort_order }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="small">
                                {{ Str::before(\App\Models\Popup::SHOW_ON[$popup->show_on] ?? $popup->show_on, ' (') }}
                                @if($popup->device !== 'all')<div class="text-muted">{{ ucfirst($popup->device) }} only</div>@endif
                            </td>
                            <td class="small">
                                @if($popup->trigger === 'delay') After {{ $popup->trigger_delay }}s
                                @elseif($popup->trigger === 'scroll') After {{ $popup->trigger_scroll }}% scroll
                                @else Exit intent @endif
                            </td>
                            <td class="small">{{ Str::before(\App\Models\Popup::FREQUENCIES[$popup->frequency] ?? $popup->frequency, ' (') }}</td>
                            <td class="small text-muted">
                                @if($popup->starts_at || $popup->ends_at)
                                    {{ $popup->starts_at?->format('d M Y') ?? 'Now' }} &rarr; {{ $popup->ends_at?->format('d M Y') ?? 'No end' }}
                                @else Always @endif
                            </td>
                            <td><span class="badge {{ $badge }}">{{ ucfirst($popup->state) }}</span></td>
                            <td class="text-end text-nowrap">
                                <a href="{{ url('/') }}?popup_preview={{ $popup->id }}" target="_blank" class="btn btn-sm btn-outline-primary">Preview</a>
                                <form action="{{ route('admin.popups.toggle', $popup) }}" method="POST" class="d-inline">
                                    @csrf @method('PATCH')
                                    <button class="btn btn-sm btn-outline-secondary">{{ $popup->status ? 'Turn off' : 'Turn on' }}</button>
                                </form>
                                <a href="{{ route('admin.popups.edit', $popup) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                                <form action="{{ route('admin.popups.destroy', $popup) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this popup?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-4">No popups yet. Click "Add Popup" to create one.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-3">{{ $popups->links() }}</div>
@endsection