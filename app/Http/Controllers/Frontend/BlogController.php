<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use App\Models\BlogCategory;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BlogController extends Controller
{
    public function index(Request $request): View
    {
        $posts = Blog::with(['category', 'author'])
            ->where('status', true)
            ->when($request->filled('category'), fn ($q) => $q->whereHas('category', fn ($c) => $c->where('slug', $request->string('category'))))
            ->latest('published_at')
            ->paginate(9);

        return view('frontend.blog.index', [
            'posts' => $posts,
            'categories' => BlogCategory::orderBy('name')->get(),
        ]);
    }

    public function show(Blog $post): View
    {
        abort_unless($post->status, 404);

        $related = Blog::where('status', true)
            ->where('blog_category_id', $post->blog_category_id)
            ->where('id', '!=', $post->id)
            ->take(3)
            ->get();

        return view('frontend.blog.show', compact('post', 'related'));
    }
}
