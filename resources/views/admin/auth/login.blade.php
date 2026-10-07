<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @php
        $siteName = \App\Models\Setting::get('company_name') ?: config('app.name');
        $siteLogo = \App\Models\Setting::get('logo');
    @endphp

    <style>
        :root {
            --brand-red: #E4002B;
            --brand-red-dark: #B5001F;
            --brand-red-light: #FFF1F3;
            --ink: #1A1A1A;
            --muted: #6B7280;
        }
        body { font-family: 'Inter', system-ui, sans-serif; color: var(--ink); background: #fff; }

        .login-wrap { min-height: 100vh; }

        /* ---------- Left brand panel ---------- */
        .login-side {
            position: relative; overflow: hidden; color: #fff;
            background: linear-gradient(145deg, #15171a 0%, #1f2327 55%, #2a0a10 100%);
            display: flex; flex-direction: column; justify-content: space-between;
            padding: 3rem;
        }
        .login-side::before, .login-side::after {
            content: ""; position: absolute; border-radius: 50%; pointer-events: none;
        }
        .login-side::before {
            width: 520px; height: 520px; right: -180px; top: -160px;
            background: radial-gradient(circle, rgba(228,0,43,.45), transparent 65%);
        }
        .login-side::after {
            width: 420px; height: 420px; left: -160px; bottom: -180px;
            background: radial-gradient(circle, rgba(228,0,43,.28), transparent 65%);
        }
        .login-side > * { position: relative; z-index: 1; }
        .side-logo { display: flex; align-items: center; gap: .75rem; font-weight: 800; font-size: 1.3rem; letter-spacing: -.02em; }
        .side-logo img { max-height: 42px; background: #fff; border-radius: 10px; padding: 6px 10px; }
        .side-logo .mark {
            width: 42px; height: 42px; border-radius: 12px; background: var(--brand-red);
            display: inline-flex; align-items: center; justify-content: center; font-size: 1.3rem;
        }
        .side-title { font-size: 2.2rem; font-weight: 800; line-height: 1.15; letter-spacing: -.02em; margin-bottom: 1rem; }
        .side-title span { color: var(--brand-red); }
        .side-text { color: rgba(255,255,255,.65); max-width: 400px; }
        .side-list { list-style: none; padding: 0; margin: 2rem 0 0; display: grid; gap: .85rem; }
        .side-list li { display: flex; align-items: center; gap: .75rem; color: rgba(255,255,255,.85); font-size: .95rem; }
        .side-list i {
            width: 34px; height: 34px; border-radius: 10px; flex-shrink: 0;
            background: rgba(255,255,255,.08); border: 1px solid rgba(255,255,255,.12);
            display: inline-flex; align-items: center; justify-content: center; color: #ff6b84;
        }
        .side-foot { color: rgba(255,255,255,.4); font-size: .8rem; }

        /* ---------- Right form ---------- */
        .login-main {
            display: flex; align-items: center; justify-content: center;
            padding: 2rem 1.25rem; background: #f7f8fa;
        }
        .login-card {
            width: 100%; max-width: 420px; background: #fff; border-radius: 18px;
            padding: 2.25rem; border: 1px solid #eceef1;
            box-shadow: 0 24px 48px -24px rgba(16,24,40,.18);
            animation: loginIn .5s ease both;
        }
        @keyframes loginIn { from { opacity: 0; transform: translateY(14px); } to { opacity: 1; transform: none; } }

        .login-badge {
            width: 54px; height: 54px; border-radius: 16px; background: var(--brand-red-light); color: var(--brand-red);
            display: inline-flex; align-items: center; justify-content: center; font-size: 1.5rem; margin-bottom: 1rem;
        }
        .login-card h1 { font-size: 1.55rem; font-weight: 800; letter-spacing: -.02em; margin-bottom: .25rem; }
        .login-card .sub { color: var(--muted); margin-bottom: 1.5rem; }

        .field-label { font-size: .82rem; font-weight: 600; margin-bottom: .35rem; }
        .field {
            display: flex; align-items: center; border: 1.5px solid #e3e6ea; border-radius: 12px;
            background: #fff; transition: border-color .15s, box-shadow .15s;
        }
        .field:focus-within { border-color: var(--brand-red); box-shadow: 0 0 0 4px rgba(228,0,43,.1); }
        .field > i.lead-icon { padding: 0 .25rem 0 .9rem; color: #9aa1ab; font-size: 1.05rem; }
        .field:focus-within > i.lead-icon { color: var(--brand-red); }
        .field input {
            flex: 1; min-width: 0; border: 0; outline: 0; background: transparent;
            padding: .8rem .9rem; font-size: .95rem; color: var(--ink);
        }
        .field .toggle-pass {
            border: 0; background: transparent; color: #9aa1ab; padding: 0 .9rem; font-size: 1.1rem; cursor: pointer;
        }
        .field .toggle-pass:hover { color: var(--ink); }
        .field.is-invalid { border-color: #dc3545; }

        .remember { display: flex; align-items: center; gap: .5rem; font-size: .88rem; color: var(--muted); cursor: pointer; margin: 0; }
        .remember input { accent-color: var(--brand-red); width: 16px; height: 16px; }

        .btn-login {
            width: 100%; border: 0; border-radius: 12px; padding: .85rem 1rem; font-weight: 700; color: #fff;
            background: linear-gradient(135deg, var(--brand-red), var(--brand-red-dark));
            box-shadow: 0 10px 20px -8px rgba(228,0,43,.55);
            transition: transform .15s ease, box-shadow .15s ease, opacity .15s;
        }
        .btn-login:hover { transform: translateY(-1px); box-shadow: 0 14px 24px -8px rgba(228,0,43,.6); }
        .btn-login:active { transform: translateY(0); }
        .btn-login[disabled] { opacity: .75; pointer-events: none; }

        .login-alert { border-radius: 12px; font-size: .9rem; padding: .7rem .9rem; display: flex; gap: .6rem; align-items: flex-start; }
        .login-alert.err  { background: #fff1f2; color: #b4232f; border: 1px solid #ffd3d7; }
        .login-alert.info { background: #eff6ff; color: #1d4ed8; border: 1px solid #cfe0ff; }

        .back-link { color: var(--muted); text-decoration: none; font-size: .88rem; }
        .back-link:hover { color: var(--ink); }

        @media (max-width: 991px) {
            .login-card { padding: 1.75rem; }
        }
        @media (prefers-reduced-motion: reduce) {
            .login-card { animation: none; }
            .btn-login { transition: none; }
        }
    </style>
</head>
<body>
<div class="container-fluid login-wrap">
    <div class="row min-vh-100">

        {{-- Left brand panel (hidden on mobile) --}}
        <div class="col-lg-6 d-none d-lg-block p-0">
            <div class="login-side h-100">
                <div class="side-logo">
                    @if($siteLogo)
                        <img src="{{ Storage::url($siteLogo) }}" alt="{{ $siteName }}">
                    @else
                        <span class="mark"><i class="bi bi-cpu"></i></span>
                        <span>{{ $siteName }}</span>
                    @endif
                </div>

                <div>
                    <h2 class="side-title">Manage your store<br>with <span>confidence.</span></h2>
                    <p class="side-text">Products, brands, banners and customer enquiries, all in one place.</p>
                    <ul class="side-list">
                        <li><i class="bi bi-box-seam"></i> Products, categories &amp; brands</li>
                        <li><i class="bi bi-images"></i> Banners &amp; homepage content</li>
                        <li><i class="bi bi-chat-dots"></i> Enquiries &amp; quote requests</li>
                    </ul>
                </div>

                <div class="side-foot">&copy; {{ date('Y') }} {{ $siteName }}. All rights reserved.</div>
            </div>
        </div>

        {{-- Right form --}}
        <div class="col-lg-6 login-main">
            <div class="login-card">

                {{-- Logo shown only on mobile, where the left panel is hidden --}}
                <div class="d-lg-none text-center mb-3">
                    @if($siteLogo)
                        <img src="{{ Storage::url($siteLogo) }}" alt="{{ $siteName }}" style="max-height:44px;">
                    @endif
                </div>

                <div class="login-badge"><i class="bi bi-shield-lock"></i></div>
                <h1>Welcome back</h1>
                <p class="sub">Sign in to the admin panel to continue.</p>

                @if(session('status'))
                    <div class="login-alert info mb-3"><i class="bi bi-info-circle"></i><div>{{ session('status') }}</div></div>
                @endif

                @if ($errors->any())
                    <div class="login-alert err mb-3"><i class="bi bi-exclamation-circle"></i><div>{{ $errors->first() }}</div></div>
                @endif

                <form method="POST" action="{{ route('admin.login') }}" id="loginForm">
                    @csrf

                    <div class="mb-3">
                        <label class="field-label" for="email">Email address</label>
                        <div class="field {{ $errors->has('email') ? 'is-invalid' : '' }}">
                            <i class="bi bi-envelope lead-icon"></i>
                            <input type="email" id="email" name="email" value="{{ old('email') }}"
                                   placeholder="you@company.com" autocomplete="username" required autofocus>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="field-label" for="password">Password</label>
                        <div class="field {{ $errors->has('password') ? 'is-invalid' : '' }}">
                            <i class="bi bi-lock lead-icon"></i>
                            <input type="password" id="password" name="password"
                                   placeholder="Enter your password" autocomplete="current-password" required>
                            <button type="button" class="toggle-pass" id="togglePass" aria-label="Show password">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <label class="remember"><input type="checkbox" name="remember" id="remember"> Remember me</label>
                    </div>

                    <button type="submit" class="btn-login" id="loginBtn">
                        <span class="label">Sign in</span>
                        <span class="spinner-border spinner-border-sm ms-2 d-none" role="status" aria-hidden="true"></span>
                    </button>
                </form>

                <div class="text-center mt-4">
                    <a href="{{ route('home') }}" class="back-link"><i class="bi bi-arrow-left me-1"></i>Back to website</a>
                </div>
            </div>
        </div>

    </div>
</div>

<script>
    // Show / hide password
    (function () {
        const input = document.getElementById('password');
        const btn = document.getElementById('togglePass');
        btn.addEventListener('click', function () {
            const show = input.type === 'password';
            input.type = show ? 'text' : 'password';
            btn.innerHTML = show ? '<i class="bi bi-eye-slash"></i>' : '<i class="bi bi-eye"></i>';
            btn.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
        });

        // Prevent double submits and show a spinner while signing in
        const form = document.getElementById('loginForm');
        const loginBtn = document.getElementById('loginBtn');
        form.addEventListener('submit', function () {
            loginBtn.setAttribute('disabled', 'disabled');
            loginBtn.querySelector('.label').textContent = 'Signing in…';
            loginBtn.querySelector('.spinner-border').classList.remove('d-none');
        });
    })();
</script>
</body>
</html>