@extends('admin.layouts.admin')
@php($isEdit = $popup->exists)
@section('title', $isEdit ? 'Edit Popup' : 'Add Popup')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0">{{ $isEdit ? 'Edit Popup' : 'Add Popup' }}</h3>
        <div class="d-flex gap-2">
            @if($isEdit)
                <a href="{{ url('/') }}?popup_preview={{ $popup->id }}" target="_blank" class="btn btn-outline-primary">Preview</a>
            @endif
            <a href="{{ route('admin.popups.index') }}" class="btn btn-outline-secondary">Back</a>
        </div>
    </div>

    @if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif

    <form method="POST" enctype="multipart/form-data"
          action="{{ $isEdit ? route('admin.popups.update', $popup) : route('admin.popups.store') }}">
        @csrf
        @if($isEdit) @method('PUT') @endif

        {{-- CONTENT --}}
        <div class="card shadow-sm mb-3">
            <div class="card-header bg-white fw-semibold">Content</div>
            <div class="card-body row g-3">
                <div class="col-md-6">
                    <label class="form-label">Name (only you see this) <span class="text-danger">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $popup->name) }}" class="form-control" placeholder="e.g. October offer" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Layout</label>
                    <select name="layout" id="layout" class="form-select">
                        @foreach(\App\Models\Popup::LAYOUTS as $k => $label)
                            <option value="{{ $k }}" @selected(old('layout', $popup->layout) === $k)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6 only-standard">
                    <label class="form-label">Title</label>
                    <input type="text" name="title" value="{{ old('title', $popup->title) }}" class="form-control" maxlength="150">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Image <span class="text-muted small" id="imgHint">(optional, 2 MB max)</span></label>
                    <input type="file" name="image" class="form-control" accept="image/*">
                    @if($popup->image)
                        <div class="mt-2 d-flex align-items-center gap-3">
                            <img src="{{ Storage::url($popup->image) }}" alt="" style="max-height:70px;border-radius:6px">
                            <div class="form-check only-standard">
                                <input class="form-check-input" type="checkbox" name="remove_image" value="1" id="remove_image">
                                <label class="form-check-label small text-danger" for="remove_image">Remove image</label>
                            </div>
                        </div>
                    @endif
                </div>
                <div class="col-12 only-standard">
                    <label class="form-label">Text</label>
                    <textarea name="body" rows="3" class="form-control" maxlength="1000">{{ old('body', $popup->body) }}</textarea>
                </div>

                <div class="col-md-4 only-standard link-fields">
                    <label class="form-label">Button text</label>
                    <input type="text" name="button_text" value="{{ old('button_text', $popup->button_text) }}" class="form-control" placeholder="e.g. Get a Quote">
                </div>
                <div class="col-md-5 link-fields">
                    <label class="form-label">Link <span class="text-muted small">(button or image link)</span></label>
                    <input type="text" name="button_url" value="{{ old('button_url', $popup->button_url) }}" class="form-control" placeholder="/products or https://...">
                </div>
                <div class="col-md-3 d-flex align-items-end link-fields">
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" name="button_new_tab" value="1" id="button_new_tab" @checked(old('button_new_tab', $popup->button_new_tab))>
                        <label class="form-check-label" for="button_new_tab">Open in new tab</label>
                    </div>
                </div>
            </div>
        </div>

        {{-- LEAD FORM --}}
<div class="card shadow-sm mb-3">
    <div class="card-header bg-white fw-semibold">Call-back form</div>
    <div class="card-body row g-3">
        <div class="col-12">
            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" role="switch" name="show_form" value="1" id="show_form" @checked(old('show_form', $popup->show_form))>
                <label class="form-check-label fw-semibold" for="show_form">Show a name and phone form in this popup</label>
            </div>
            <div class="form-text">Visitors enter their name and number, and you reply manually. See them under Popup Leads. No link or redirect is needed.</div>
        </div>

        <div class="col-md-4 form-fields">
            <label class="form-label">Button text</label>
            <input type="text" name="form_button_text" value="{{ old('form_button_text', $popup->form_button_text ?? 'Request a Call Back') }}" class="form-control" maxlength="60">
        </div>
        <div class="col-md-8 form-fields">
            <label class="form-label">Message after sending</label>
            <input type="text" name="success_message" value="{{ old('success_message', $popup->success_message) }}" class="form-control" maxlength="255" placeholder="Thank you! We will get back to you shortly.">
        </div>

        <div class="col-md-8 form-fields">
            <label class="form-label">Privacy note <span class="text-muted small">(small text under the button)</span></label>
            <input type="text" name="consent_text" value="{{ old('consent_text', $popup->consent_text ?? 'We will only use your details to contact you about your enquiry.') }}" class="form-control" maxlength="255">
        </div>
        <div class="col-md-4 form-fields">
            <label class="form-label">Privacy Policy link</label>
            <input type="text" name="privacy_url" value="{{ old('privacy_url', $popup->privacy_url) }}" class="form-control" placeholder="/privacy-policy">
        </div>
        <div class="col-12 form-fields">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="consent_required" value="1" id="consent_required" @checked(old('consent_required', $popup->consent_required))>
                <label class="form-check-label" for="consent_required">Make visitors tick a consent box before sending <span class="text-muted small">(optional, usually not needed)</span></label>
            </div>
        </div>
    </div>
</div>

        {{-- WHEN TO SHOW --}}
        <div class="card shadow-sm mb-3">
            <div class="card-header bg-white fw-semibold">When should it pop up?</div>
            <div class="card-body row g-3">
                <div class="col-md-4">
                    <label class="form-label">Trigger</label>
                    <select name="trigger" id="trigger" class="form-select">
                        @foreach(\App\Models\Popup::TRIGGERS as $k => $label)
                            <option value="{{ $k }}" @selected(old('trigger', $popup->trigger) === $k)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 trigger-delay">
                    <label class="form-label">Wait (seconds)</label>
                    <input type="number" name="trigger_delay" min="0" max="120" value="{{ old('trigger_delay', $popup->trigger_delay ?? 3) }}" class="form-control">
                </div>
                <div class="col-md-4 trigger-scroll">
                    <label class="form-label">Scroll depth (%)</label>
                    <input type="number" name="trigger_scroll" min="5" max="100" value="{{ old('trigger_scroll', $popup->trigger_scroll ?? 50) }}" class="form-control">
                </div>
                <div class="col-md-4 trigger-exit">
                    <div class="form-text pt-4">Exit intent works on desktop. On phones and tablets it shows after 15 seconds instead.</div>
                </div>

                <div class="col-md-6">
                    <label class="form-label">How often does each visitor see it?</label>
                    <select name="frequency" class="form-select">
                        @foreach(\App\Models\Popup::FREQUENCIES as $k => $label)
                            <option value="{{ $k }}" @selected(old('frequency', $popup->frequency) === $k)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <div class="form-text">Saving changes to a popup shows it again to visitors who have already seen it.</div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Devices</label>
                    <select name="device" class="form-select">
                        @foreach(\App\Models\Popup::DEVICES as $k => $label)
                            <option value="{{ $k }}" @selected(old('device', $popup->device) === $k)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Pages</label>
                    <select name="show_on" id="show_on" class="form-select">
                        @foreach(\App\Models\Popup::SHOW_ON as $k => $label)
                            <option value="{{ $k }}" @selected(old('show_on', $popup->show_on) === $k)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6 show-custom">
                    <label class="form-label">Page paths (one per line)</label>
                    <textarea name="custom_paths" rows="3" class="form-control" placeholder="/&#10;about&#10;blog/*">{{ old('custom_paths', $popup->custom_paths) }}</textarea>
                    <div class="form-text">Use <code>/</code> for the home page and <code>*</code> as a wildcard, e.g. <code>blog/*</code>.</div>
                </div>
            </div>
        </div>

        {{-- SCHEDULE --}}
        <div class="card shadow-sm mb-3">
            <div class="card-header bg-white fw-semibold">Schedule and status</div>
            <div class="card-body row g-3">
                <div class="col-md-4">
                    <label class="form-label">Start (optional)</label>
                    <input type="datetime-local" name="starts_at" value="{{ old('starts_at', $popup->starts_at?->format('Y-m-d\TH:i')) }}" class="form-control">
                </div>
                <div class="col-md-4">
                    <label class="form-label">End (optional)</label>
                    <input type="datetime-local" name="ends_at" value="{{ old('ends_at', $popup->ends_at?->format('Y-m-d\TH:i')) }}" class="form-control">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Priority</label>
                    <input type="number" name="sort_order" min="0" value="{{ old('sort_order', $popup->sort_order ?? 0) }}" class="form-control">
                    <div class="form-text">Lowest number wins.</div>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <div class="form-check form-switch mb-2">
                        <input class="form-check-input" type="checkbox" role="switch" name="status" value="1" id="status" @checked(old('status', $popup->status ?? true))>
                        <label class="form-check-label" for="status">Active</label>
                    </div>
                </div>
            </div>
        </div>

        <button class="btn btn-primary">{{ $isEdit ? 'Save changes' : 'Create popup' }}</button>
    </form>
@endsection

@push('scripts')
<script>
    (function () {
        var $ = function (s) { return document.querySelector(s); };
        var all = function (s) { return document.querySelectorAll(s); };

        function sync() {
            var layout = $('#layout').value, trigger = $('#trigger').value, showOn = $('#show_on').value;
            var showForm = $('#show_form').checked;

            all('.only-standard').forEach(function (el) { el.style.display = layout === 'standard' ? '' : 'none'; });
            all('.link-fields').forEach(function (el) {
                if (showForm) el.style.display = 'none';
                else if (!el.classList.contains('only-standard')) el.style.display = '';
            });
            all('.form-fields').forEach(function (el) { el.style.display = showForm ? '' : 'none'; });

            $('#imgHint').textContent = layout === 'image_only' ? '(required, 2 MB max)' : '(optional, 2 MB max)';
            all('.trigger-delay').forEach(function (el) { el.style.display = trigger === 'delay' ? '' : 'none'; });
            all('.trigger-scroll').forEach(function (el) { el.style.display = trigger === 'scroll' ? '' : 'none'; });
            all('.trigger-exit').forEach(function (el) { el.style.display = trigger === 'exit_intent' ? '' : 'none'; });
            all('.show-custom').forEach(function (el) { el.style.display = showOn === 'custom' ? '' : 'none'; });
        }
        ['#layout', '#trigger', '#show_on', '#show_form'].forEach(function (s) { $(s).addEventListener('change', sync); });
        sync();
    })();
</script>
@endpush