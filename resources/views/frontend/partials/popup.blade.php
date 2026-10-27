@php
    $isPreview = false;
    $popup = null;

    // Logged-in admins can force a popup with ?popup_preview=ID (ignores trigger, schedule and frequency)
    if (request()->filled('popup_preview') && auth()->check()) {
        $popup = \App\Models\Popup::find(request('popup_preview'));
        $isPreview = (bool) $popup;
    } else {
        $popup = \App\Models\Popup::forRequest(request());
    }

    if ($popup) {
        $hasNote = $popup->consent_text || $popup->privacy_url;
        $noteText = $popup->consent_text;
    }
@endphp

@if($popup)
<style>
    .site-popup .modal-dialog { max-width: 460px; }
    .site-popup .modal-dialog.popup-wide { max-width: 720px; }
    .site-popup .modal-content { border: 0; border-radius: 16px; overflow: hidden; box-shadow: 0 24px 60px rgba(0,0,0,.28); }
    .site-popup-close { position: absolute; top: 12px; right: 12px; z-index: 5; background-color: #fff; border-radius: 50%; padding: .7rem; opacity: 1; box-shadow: 0 2px 8px rgba(0,0,0,.2); }
    .site-popup-close:hover { background-color: #f3f4f6; }
    .site-popup-img { display: block; width: 100%; max-height: 260px; object-fit: cover; }
    .site-popup-full { display: block; width: 100%; height: auto; }
    .site-popup-body { padding: 1.75rem 1.75rem 2rem; text-align: center; }
    .site-popup-body h2 { font-size: 1.5rem; font-weight: 800; color: var(--ink); margin-bottom: .5rem; }
    .site-popup-body p { color: var(--muted); margin-bottom: 1.25rem; }
    .site-popup-body .btn { padding: .7rem 1.75rem; font-weight: 600; border-radius: 999px; }

    .site-popup-form { max-width: 380px; margin: 0 auto; text-align: left; }
    .site-popup-form .form-control { padding: .7rem .9rem; border-radius: 10px; margin-bottom: .65rem; }
    .site-popup-form .form-control:focus { border-color: var(--brand-red); box-shadow: 0 0 0 .2rem rgba(228,0,43,.15); }
    .site-popup-form .btn { width: 100%; }
    .site-popup-consent { display: flex; gap: .5rem; align-items: flex-start; font-size: .8rem; color: var(--muted); margin: .25rem 0 .75rem; }
    .site-popup-consent input { margin-top: .2rem; flex-shrink: 0; }
    .site-popup-error { color: #b42318; font-size: .85rem; min-height: 1.1rem; margin-top: .5rem; text-align: center; }
    .site-popup-note { font-size: .78rem; color: var(--muted); margin: .5rem 0 0; text-align: center; }
    .site-popup-note a, .site-popup-consent a { color: var(--brand-red) !important; text-decoration: underline; }
    .site-popup-success { padding: 1rem 0; text-align: center; }
    .site-popup-success i { font-size: 2.75rem; color: #16a34a; }
    .site-popup-success p { margin: .75rem 0 0; color: var(--ink); font-weight: 600; }

    @media (max-width: 575.98px) { .site-popup-body { padding: 1.25rem 1.25rem 1.5rem; } }
</style>

<div class="modal fade site-popup" id="sitePopup" tabindex="-1" aria-hidden="true"
     @if($popup->title) aria-labelledby="sitePopupTitle" @else aria-label="Promotion" @endif
     data-popup-id="{{ $popup->id }}"
     data-version="{{ $popup->updated_at?->timestamp }}"
     data-trigger="{{ $popup->trigger }}"
     data-delay="{{ $popup->trigger_delay }}"
     data-scroll="{{ $popup->trigger_scroll }}"
     data-frequency="{{ $popup->frequency }}"
     data-device="{{ $popup->device }}"
     data-preview="{{ $isPreview ? 1 : 0 }}">
    <div class="modal-dialog modal-dialog-centered {{ $popup->layout === 'image_only' ? 'popup-wide' : '' }}">
        <div class="modal-content">
            <button type="button" class="btn-close site-popup-close" data-bs-dismiss="modal" aria-label="Close"></button>

            @if($popup->layout === 'image_only' && $popup->image)
                @if($popup->button_url && ! $popup->show_form)
                    <a href="{{ $popup->button_url }}" @if($popup->button_new_tab) target="_blank" rel="noopener" @endif aria-label="{{ $popup->title ?: $popup->name }}">
                        <img src="{{ Storage::url($popup->image) }}" alt="{{ $popup->title ?: $popup->name }}" class="site-popup-full">
                    </a>
                @else
                    <img src="{{ Storage::url($popup->image) }}" alt="{{ $popup->title ?: $popup->name }}" class="site-popup-full">
                @endif
            @elseif($popup->image)
                <img src="{{ Storage::url($popup->image) }}" alt="" class="site-popup-img">
            @endif

            @if($popup->layout === 'standard' || $popup->show_form)
                <div class="site-popup-body">
                    <div class="site-popup-main">
                        @if($popup->layout === 'standard')
                            @if($popup->title)<h2 id="sitePopupTitle">{{ $popup->title }}</h2>@endif
                            @if($popup->body)<p>{!! nl2br(e($popup->body)) !!}</p>@endif
                        @endif

                        @if($popup->show_form)
                            <form class="site-popup-form" method="POST" action="{{ route('popup-lead.store') }}" novalidate>
                                @csrf
                                <input type="hidden" name="popup_id" value="{{ $popup->id }}">
                                <input type="hidden" name="page_url" value="">
                                {{-- honeypot, hidden from real visitors --}}
                                <input type="text" name="website" class="d-none" tabindex="-1" autocomplete="off" aria-hidden="true">

                                <input type="text" name="name" class="form-control" placeholder="Your name" maxlength="100" autocomplete="name" required>
                                <input type="tel" name="phone" class="form-control" placeholder="Phone / WhatsApp number" maxlength="20" autocomplete="tel" required>

                                @if($popup->consent_required)
                                    <label class="site-popup-consent">
                                        <input type="checkbox" name="consent" value="1">
                                        <span>{{ $noteText ?: 'I agree to be contacted about my enquiry.' }}
                                            @if($popup->privacy_url)<a href="{{ $popup->privacy_url }}" target="_blank" rel="noopener">Privacy Policy</a>@endif
                                        </span>
                                    </label>
                                @endif

                                <button type="submit" class="btn btn-brand">{{ $popup->form_button_text ?: 'Request a Call Back' }}</button>
                                <div class="site-popup-error" role="alert"></div>

                                @if($hasNote && ! $popup->consent_required)
                                    <p class="site-popup-note">
                                        {{ $noteText }}
                                        @if($popup->privacy_url)<a href="{{ $popup->privacy_url }}" target="_blank" rel="noopener">Privacy Policy</a>@endif
                                    </p>
                                @endif
                            </form>
                        @elseif($popup->button_text && $popup->button_url)
                            <a href="{{ $popup->button_url }}" class="btn btn-brand"
                               @if($popup->button_new_tab) target="_blank" rel="noopener" @endif>{{ $popup->button_text }}</a>
                        @endif
                    </div>

                    @if($popup->show_form)
                        <div class="site-popup-success d-none" role="status">
                            <i class="bi bi-check-circle-fill"></i>
                            <p></p>
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>

<script>
    window.addEventListener('load', function () {
        var el = document.getElementById('sitePopup');
        if (!el || !window.bootstrap) return;

        var d = el.dataset;
        var preview = d.preview === '1';
        var isDesktop = window.matchMedia('(min-width: 768px)').matches;
        var key = 'popup_' + d.popupId + '_' + d.version;
        var doneKey = key + '_done';

        function elapsed(ms) {
            var t = parseInt(localStorage.getItem(key) || '0', 10);
            return !t || (Date.now() - t) > ms;
        }
        function canShow() {
            try {
                if (localStorage.getItem(doneKey)) return false; // already sent the form
                switch (d.frequency) {
                    case 'once_per_session': return !sessionStorage.getItem(key);
                    case 'once_ever':        return !localStorage.getItem(key);
                    case 'once_per_day':     return elapsed(24 * 3600 * 1000);
                    case 'once_per_week':    return elapsed(7 * 24 * 3600 * 1000);
                }
            } catch (e) {}
            return true;
        }
        function remember() {
            try {
                if (d.frequency === 'once_per_session') sessionStorage.setItem(key, '1');
                else if (d.frequency !== 'every_visit') localStorage.setItem(key, String(Date.now()));
            } catch (e) {}
        }

        var modal = bootstrap.Modal.getOrCreateInstance(el);

        // ---- Call-back form (AJAX, no page change) ----
        var form = el.querySelector('.site-popup-form');
        if (form) {
            var errorBox = form.querySelector('.site-popup-error');
            var btn = form.querySelector('button[type="submit"]');
            var btnText = btn.textContent;

            form.addEventListener('submit', function (e) {
                e.preventDefault();
                errorBox.textContent = '';

                var name = form.elements['name'].value.trim();
                var phone = form.elements['phone'].value.trim();
                if (!name) { errorBox.textContent = 'Please enter your name.'; return; }
                if (!/^[0-9+\-\s()]{7,20}$/.test(phone)) { errorBox.textContent = 'Please enter a valid phone number.'; return; }
                if (form.elements['consent'] && !form.elements['consent'].checked) { errorBox.textContent = 'Please tick the box to continue.'; return; }

                form.elements['page_url'].value = window.location.href;
                btn.disabled = true;
                btn.textContent = 'Sending...';

                fetch(form.action, {
                    method: 'POST',
                    body: new FormData(form),
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    credentials: 'same-origin'
                })
                .then(function (res) {
                    return res.json().catch(function () { return {}; }).then(function (json) { return { res: res, json: json }; });
                })
                .then(function (r) {
                    if (r.res.ok) {
                        el.querySelector('.site-popup-main').classList.add('d-none');
                        var ok = el.querySelector('.site-popup-success');
                        ok.querySelector('p').textContent = r.json.message || 'Thank you! We will get back to you shortly.';
                        ok.classList.remove('d-none');
                        if (!preview) { try { localStorage.setItem(doneKey, '1'); } catch (e) {} }
                        setTimeout(function () { modal.hide(); }, 6000);
                        return;
                    }
                    var msg = 'Something went wrong. Please try again.';
                    if (r.res.status === 422 && r.json.errors) {
                        msg = Object.values(r.json.errors)[0][0];
                    } else if (r.res.status === 429) {
                        msg = 'Too many attempts. Please try again in a minute.';
                    }
                    errorBox.textContent = msg;
                    btn.disabled = false;
                    btn.textContent = btnText;
                })
                .catch(function () {
                    errorBox.textContent = 'Network error. Please try again.';
                    btn.disabled = false;
                    btn.textContent = btnText;
                });
            });
        }

        // ---- When to show ----
        if (!preview) {
            if (d.device === 'desktop' && !isDesktop) return;
            if (d.device === 'mobile' && isDesktop) return;
            if (!canShow()) return;
        }

        var shown = false;
        var cleanups = [];

        function show() {
            if (shown) return;
            if (document.querySelector('.modal.show')) return;
            shown = true;
            modal.show();
            if (!preview) remember();
            cleanups.forEach(function (fn) { fn(); });
        }

        if (preview) { show(); return; }

        function byDelay(sec) {
            var t = setTimeout(show, sec * 1000);
            cleanups.push(function () { clearTimeout(t); });
        }

        if (d.trigger === 'scroll') {
            var pct = parseInt(d.scroll, 10) || 50;
            var range = document.documentElement.scrollHeight - window.innerHeight;
            if (range <= 0) { byDelay(5); }
            else {
                var onScroll = function () {
                    if ((window.scrollY / range) * 100 >= pct) show();
                };
                window.addEventListener('scroll', onScroll, { passive: true });
                cleanups.push(function () { window.removeEventListener('scroll', onScroll); });
            }
        } else if (d.trigger === 'exit_intent') {
            if (isDesktop) {
                var onOut = function (e) { if (!e.relatedTarget && e.clientY <= 0) show(); };
                document.addEventListener('mouseout', onOut);
                cleanups.push(function () { document.removeEventListener('mouseout', onOut); });
            } else {
                byDelay(15);
            }
        } else {
            byDelay(parseInt(d.delay, 10) || 0);
        }
    });
</script>
@endif