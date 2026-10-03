@php
    // Digits only, so "+971 50 123 4567" in Settings still works
    $waNumber   = preg_replace('/\D/', '', \App\Models\Setting::get('whatsapp_number', '971500000000'));
    $waCompany  = \App\Models\Setting::get('company_name', config('app.name'));
    $waGreeting = \App\Models\Setting::get('whatsapp_greeting', 'Hello! 👋 How can we help you today?');
    $waLogo     = \App\Models\Setting::get('logo');
@endphp

<div id="waWidget" class="wa-widget" data-number="{{ $waNumber }}" data-company="{{ $waCompany }}">

    {{-- Chat popup --}}
    <div class="wa-popup" id="waPopup" role="dialog" aria-label="Chat on WhatsApp" aria-hidden="true">
        <div class="wa-header">
            <div class="wa-avatar">
                @if($waLogo)
                    <img src="{{ Storage::url($waLogo) }}" alt="">
                @else
                    {{ strtoupper(mb_substr($waCompany, 0, 1)) }}
                @endif
                <span class="wa-online"></span>
            </div>
            <div class="wa-title">
                <div class="wa-name">{{ $waCompany }}</div>
                <div class="wa-status">Online · Typically replies in minutes</div>
            </div>
            <button type="button" class="wa-close" id="waClose" aria-label="Close chat"><i class="bi bi-x-lg"></i></button>
        </div>

        <div class="wa-body">
            <div class="wa-typing" id="waTyping"><span></span><span></span><span></span></div>
            <div class="wa-bubble" id="waBubble">
                {{ $waGreeting }}
                <span class="wa-time" id="waTime"></span>
            </div>
        </div>

        <form class="wa-footer" id="waForm" autocomplete="off">
            <input type="text" id="waInput" placeholder="Type a message…" aria-label="Type your message" maxlength="500">
            <button type="submit" class="wa-send" aria-label="Send on WhatsApp"><i class="bi bi-send-fill"></i></button>
        </form>
    </div>

    {{-- Floating button --}}
    <button type="button" class="wa-fab" id="waFab" aria-label="Chat on WhatsApp" aria-expanded="false" aria-controls="waPopup">
        <i class="bi bi-whatsapp wa-icon-open"></i>
        <i class="bi bi-x-lg wa-icon-close"></i>
        <span class="wa-badge" id="waBadge">1</span>
    </button>
</div>

<style>
    .wa-widget { position: fixed; right: 20px; bottom: 20px; z-index: 1050; }

    /* Floating button */
    .wa-fab {
        position: relative; width: 60px; height: 60px; border: 0; border-radius: 50%;
        background: #25D366; color: #fff; font-size: 30px; cursor: pointer;
        display: flex; align-items: center; justify-content: center;
        box-shadow: 0 6px 18px rgba(0,0,0,.25);
        transition: transform .2s ease, background .2s ease;
    }
    .wa-fab:hover { transform: scale(1.07); background: #20bd5a; }
    .wa-fab::before {
        content: ''; position: absolute; inset: 0; border-radius: 50%;
        background: #25D366; opacity: .5; z-index: -1; animation: waPulse 2s infinite;
    }
    .wa-icon-close { display: none; font-size: 22px; }
    .wa-widget.is-open .wa-icon-open  { display: none; }
    .wa-widget.is-open .wa-icon-close { display: block; }
    .wa-widget.is-open .wa-fab::before { animation: none; opacity: 0; }
    .wa-badge {
        position: absolute; top: -3px; right: -3px; min-width: 20px; height: 20px; padding: 0 5px;
        border-radius: 10px; background: #ff3b30; color: #fff; font-size: 12px; font-weight: 700;
        border: 2px solid #fff; display: flex; align-items: center; justify-content: center;
    }

    /* Popup */
    .wa-popup {
        position: absolute; right: 0; bottom: 76px; width: 340px; max-width: calc(100vw - 40px);
        border-radius: 16px; overflow: hidden; background: #fff;
        box-shadow: 0 12px 40px rgba(0,0,0,.28);
        opacity: 0; visibility: hidden; transform: translateY(16px) scale(.96); transform-origin: bottom right;
        transition: opacity .25s ease, transform .25s ease, visibility .25s;
    }
    .wa-widget.is-open .wa-popup { opacity: 1; visibility: visible; transform: none; }

    .wa-header { display: flex; align-items: center; gap: .75rem; padding: .85rem 1rem; background: #075E54; color: #fff; }
    .wa-avatar {
        position: relative; flex-shrink: 0; width: 44px; height: 44px; border-radius: 50%;
        background: #fff; color: #075E54; font-weight: 700; font-size: 1.2rem;
        display: flex; align-items: center; justify-content: center;
    }
    .wa-avatar img { width: 100%; height: 100%; border-radius: 50%; object-fit: contain; padding: 6px; }
    .wa-online {
        position: absolute; right: 0; bottom: 0; width: 12px; height: 12px; border-radius: 50%;
        background: #25D366; border: 2px solid #075E54;
    }
    .wa-title { flex: 1; min-width: 0; }
    .wa-name { font-weight: 600; line-height: 1.2; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .wa-status { font-size: .75rem; opacity: .85; }
    .wa-close { background: none; border: 0; color: #fff; opacity: .8; font-size: 1rem; padding: .25rem; cursor: pointer; }
    .wa-close:hover { opacity: 1; }

    /* Chat area */
    .wa-body {
        min-height: 140px; padding: 1.25rem 1rem; background-color: #e5ddd5;
        background-image: radial-gradient(rgba(0,0,0,.05) 1px, transparent 1px); background-size: 14px 14px;
    }
    .wa-bubble {
        display: none; position: relative; max-width: 85%; padding: .55rem .7rem 1.4rem;
        background: #fff; color: #111; font-size: .92rem; line-height: 1.4;
        border-radius: 0 10px 10px 10px; box-shadow: 0 1px 1px rgba(0,0,0,.13);
    }
    .wa-bubble.show { display: block; animation: waIn .3s ease; }
    .wa-bubble::before { content: ''; position: absolute; top: 0; left: -8px; border-top: 8px solid #fff; border-left: 8px solid transparent; }
    .wa-time { position: absolute; right: .55rem; bottom: .3rem; font-size: .68rem; color: #667781; }

    .wa-typing {
        display: none; align-items: center; gap: 4px; padding: .7rem .85rem;
        background: #fff; border-radius: 0 10px 10px 10px; box-shadow: 0 1px 1px rgba(0,0,0,.13);
    }
    .wa-typing.show { display: inline-flex; }
    .wa-typing span { width: 7px; height: 7px; border-radius: 50%; background: #9aa5ab; animation: waDot 1.2s infinite ease-in-out; }
    .wa-typing span:nth-child(2) { animation-delay: .15s; }
    .wa-typing span:nth-child(3) { animation-delay: .3s; }

    /* Message box */
    .wa-footer { display: flex; align-items: center; gap: .5rem; padding: .6rem; background: #f0f2f5; }
    .wa-footer input {
        flex: 1; min-width: 0; border: 0; outline: 0; border-radius: 22px;
        padding: .6rem 1rem; font-size: .92rem; background: #fff;
    }
    .wa-footer input:focus { box-shadow: 0 0 0 2px rgba(37,211,102,.45); }
    .wa-send {
        flex-shrink: 0; width: 42px; height: 42px; border: 0; border-radius: 50%;
        background: #25D366; color: #fff; cursor: pointer;
        display: flex; align-items: center; justify-content: center;
        transition: background .2s ease, transform .2s ease;
    }
    .wa-send:hover { background: #20bd5a; transform: scale(1.05); }

    @keyframes waPulse { 0% { transform: scale(1); opacity: .5; } 100% { transform: scale(1.6); opacity: 0; } }
    @keyframes waIn    { from { opacity: 0; transform: translateY(8px) scale(.96); } to { opacity: 1; transform: none; } }
    @keyframes waDot   { 0%, 60%, 100% { transform: translateY(0); opacity: .5; } 30% { transform: translateY(-4px); opacity: 1; } }

    @media (max-width: 575.98px) {
        .wa-widget { right: 14px; bottom: 14px; }
        .wa-fab { width: 56px; height: 56px; }
        .wa-popup { bottom: 70px; width: calc(100vw - 28px); max-width: none; }
    }
    @media (prefers-reduced-motion: reduce) {
        .wa-fab::before, .wa-typing span { animation: none; }
        .wa-popup { transition: none; }
    }
</style>

<script>
(function () {
    const root = document.getElementById('waWidget');
    if (!root) return;

    const fab = document.getElementById('waFab'),
          popup = document.getElementById('waPopup'),
          closeBtn = document.getElementById('waClose'),
          form = document.getElementById('waForm'),
          input = document.getElementById('waInput'),
          typing = document.getElementById('waTyping'),
          bubble = document.getElementById('waBubble'),
          badge = document.getElementById('waBadge'),
          timeEl = document.getElementById('waTime');
    const number = root.dataset.number, company = root.dataset.company;
    let greeted = false;

    function openChat() {
        root.classList.add('is-open');
        popup.setAttribute('aria-hidden', 'false');
        fab.setAttribute('aria-expanded', 'true');
        badge.style.display = 'none';
        if (!greeted) {
            greeted = true;
            timeEl.textContent = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
            typing.classList.add('show');
            setTimeout(function () { typing.classList.remove('show'); bubble.classList.add('show'); }, 1200);
        }
        setTimeout(function () { input.focus(); }, 250);
    }

    function closeChat() {
        root.classList.remove('is-open');
        popup.setAttribute('aria-hidden', 'true');
        fab.setAttribute('aria-expanded', 'false');
    }

    fab.addEventListener('click', function () {
        root.classList.contains('is-open') ? closeChat() : openChat();
    });
    closeBtn.addEventListener('click', closeChat);
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape') closeChat(); });

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        const text = input.value.trim() || ('Hello ' + company + ', I would like to know more about your products.');
        window.open('https://wa.me/' + number + '?text=' + encodeURIComponent(text), '_blank', 'noopener');
        input.value = '';
    });
})();
</script>