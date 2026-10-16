@php
    $favicon = \App\Models\Setting::get('favicon');
    $favTypes = [
        'ico' => 'image/x-icon', 'png' => 'image/png', 'svg' => 'image/svg+xml',
        'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'webp' => 'image/webp', 'gif' => 'image/gif',
    ];
    $favType = $favTypes[strtolower(pathinfo((string) $favicon, PATHINFO_EXTENSION))] ?? 'image/png';
@endphp
@if($favicon)
    <link rel="icon" type="{{ $favType }}" href="{{ Storage::url($favicon) }}">
    <link rel="apple-touch-icon" href="{{ Storage::url($favicon) }}">
@else
    <link rel="icon" href="{{ asset('favicon.ico') }}">
@endif