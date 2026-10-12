<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Faq;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FaqController extends Controller
{
    public function index(Request $request): View
    {
        $faqs = Faq::with('categories')
            ->when($request->filled('category_id'), fn ($q) => $q->whereHas(
                'categories', fn ($c) => $c->where('categories.id', $request->integer('category_id'))
            ))
            ->orderBy('sort_order')
            ->orderBy('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.faqs.index', [
            'faqs' => $faqs,
            'categories' => $this->categoryOptions(),
        ]);
    }

    public function create(): View
    {
        return view('admin.faqs.form', [
            'faq' => new Faq(),
            'categories' => $this->categoryOptions(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $categoryIds = $data['category_ids'] ?? [];
        unset($data['category_ids']);

        $faq = Faq::create($data);
        $faq->categories()->sync($categoryIds);

        return redirect()->route('admin.faqs.index')->with('status', 'FAQ created.');
    }

    public function edit(Faq $faq): View
    {
        $faq->load('categories');

        return view('admin.faqs.form', [
            'faq' => $faq,
            'categories' => $this->categoryOptions(),
        ]);
    }

    public function update(Request $request, Faq $faq): RedirectResponse
    {
        $data = $this->validated($request);
        $categoryIds = $data['category_ids'] ?? [];
        unset($data['category_ids']);

        $faq->update($data);
        $faq->categories()->sync($categoryIds);

        return redirect()->route('admin.faqs.index')->with('status', 'FAQ updated.');
    }

    public function destroy(Faq $faq): RedirectResponse
    {
        $faq->delete();

        return redirect()->route('admin.faqs.index')->with('status', 'FAQ deleted.');
    }

    protected function validated(Request $request): array
    {
        $data = $request->validate([
            'question'       => ['required', 'string', 'max:255'],
            'answer'         => ['required', 'string', 'max:5000'],
            'sort_order'     => ['nullable', 'integer', 'min:0'],
            'category_ids'   => ['nullable', 'array'],
            'category_ids.*' => ['integer', 'exists:categories,id'],
        ]);

        $data['sort_order'] = $data['sort_order'] ?? 0;
        $data['status'] = $request->boolean('status');

        return $data;
    }

    protected function categoryOptions()
    {
        return Category::with('parent')->orderBy('name')->get();
    }
}