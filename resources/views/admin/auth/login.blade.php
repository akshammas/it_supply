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

    @vite(['resources/css/app.css', 'resources/css/admin-login.css', 'resources/js/app.js'])
    
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