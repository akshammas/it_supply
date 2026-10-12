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