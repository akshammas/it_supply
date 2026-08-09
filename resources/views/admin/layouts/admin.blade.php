<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin') | {{ config('app.name') }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-light">
<div class="d-flex">
    <nav class="d-flex flex-column p-3 bg-dark text-white" style="width:240px; min-height:100vh;">
        <a href="{{ route('admin.dashboard') }}" class="d-flex align-items-center mb-3 text-white text-decoration-none fs-5 fw-bold">
            {{ config('app.name') }}
        </a>
        <hr>
        <ul class="nav nav-pills flex-column mb-auto">
            <li class="nav-item"><a href="{{ route('admin.dashboard') }}" class="nav-link text-white"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a></li>

            <li class="nav-item mt-2"><span class="text-uppercase small text-white-50">Catalogue</span></li>
<li class="nav-item"><a href="{{ route('admin.products.index') }}" class="nav-link {{ request()->routeIs('admin.products.*') ? 'text-white' : 'text-white-50' }}"><i class="bi bi-box-seam me-2"></i>Products</a></li>
<li class="nav-item"><a href="{{ route('admin.categories.index') }}" class="nav-link {{ request()->routeIs('admin.categories.*') ? 'text-white' : 'text-white-50' }}"><i class="bi bi-diagram-3 me-2"></i>Categories</a></li>
<li class="nav-item"><a href="{{ route('admin.brands.index') }}" class="nav-link {{ request()->routeIs('admin.brands.*') ? 'text-white' : 'text-white-50' }}"><i class="bi bi-award me-2"></i>Brands</a></li>
<li class="nav-item"><a href="{{ route('admin.specifications.index') }}" class="nav-link {{ request()->routeIs('admin.specifications.*') ? 'text-white' : 'text-white-50' }}"><i class="bi bi-list-check me-2"></i>Specifications</a></li>
<li class="nav-item"><a href="#" class="nav-link text-white-50"><i class="bi bi-upload me-2"></i>Import / Export</a></li>

            <li class="nav-item mt-2"><span class="text-uppercase small text-white-50">Sales</span></li>
            <li class="nav-item"><a href="#" class="nav-link text-white-50"><i class="bi bi-envelope me-2"></i>Enquiries</a></li>
            <li class="nav-item"><a href="#" class="nav-link text-white-50"><i class="bi bi-people me-2"></i>Customers</a></li>
            <li class="nav-item"><a href="#" class="nav-link text-white-50"><i class="bi bi-file-earmark-text me-2"></i>Quotes</a></li>

            <li class="nav-item mt-2"><span class="text-uppercase small text-white-50">Content</span></li>
            <li class="nav-item"><a href="#" class="nav-link text-white-50"><i class="bi bi-lightbulb me-2"></i>Solutions</a></li>
            <li class="nav-item"><a href="#" class="nav-link text-white-50"><i class="bi bi-journal-text me-2"></i>Blog</a></li>
            <li class="nav-item"><a href="#" class="nav-link text-white-50"><i class="bi bi-file-earmark me-2"></i>Pages</a></li>
            <li class="nav-item"><a href="#" class="nav-link text-white-50"><i class="bi bi-images me-2"></i>Banners</a></li>

            <li class="nav-item mt-2"><span class="text-uppercase small text-white-50">System</span></li>
            @if(auth()->user()?->role === 'super_admin')
            <li class="nav-item"><a href="#" class="nav-link text-white-50"><i class="bi bi-person-badge me-2"></i>Admin Users</a></li>
            @endif
            <li class="nav-item"><a href="#" class="nav-link text-white-50"><i class="bi bi-gear me-2"></i>Settings</a></li>
        </ul>
        <hr>
        <div class="dropdown">
            <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle" data-bs-toggle="dropdown">
                <i class="bi bi-person-circle me-2"></i>{{ auth()->user()?->name }}
            </a>
            <ul class="dropdown-menu dropdown-menu-dark text-small shadow">
                <li>
                    <form method="POST" action="{{ route('admin.logout') }}">
                        @csrf
                        <button type="submit" class="dropdown-item">Sign out</button>
                    </form>
                </li>
            </ul>
        </div>
    </nav>

    <main class="flex-grow-1 p-4">
        @if(session('status'))
            <div class="alert alert-success">{{ session('status') }}</div>
        @endif

        @yield('content')
    </main>
</div>
</body>
</html>