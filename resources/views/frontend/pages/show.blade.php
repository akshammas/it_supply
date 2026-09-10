@extends('frontend.layouts.app')
@section('title', $page->meta_title ?? $page->title.' | '.config('app.name'))
@section('meta_description', $page->meta_description ?? '')

@section('content')
<div class="container py-5" style="max-width:800px;">
    @if($page->featured_image)
        <img src="{{ Storage::url($page->featured_image) }}" class="img-fluid rounded mb-4">
    @endif
    <h1 class="mb-4">{{ $page->title }}</h1>
    <div>{!! nl2br(e($page->content)) !!}</div>
</div>
@endsection
