<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Solution;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class SolutionController extends Controller
{
    public function index(): View
    {
        $solutions = Solution::withCount('products')->latest()->paginate(20);

        return view('admin.solutions.index', compact('solutions'));
    }

    public function create(): View
    {
        return view('admin.solutions.form', [
            'solution' => new Solution(),
            'products' => Product::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data['slug'] = $data['slug'] ?: Str::slug($data['name']);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('solutions', 'public');
        }

        $solution = Solution::create($data);
        $solution->products()->sync($request->input('product_ids', []));

        return redirect()->route('admin.solutions.index')->with('status', 'Solution created.');
    }

    public function edit(Solution $solution): View
    {
        return view('admin.solutions.form', [
            'solution' => $solution,
            'products' => Product::orderBy('name')->get(),
            'selectedProductIds' => $solution->products()->pluck('products.id')->toArray(),
        ]);
    }

    public function update(Request $request, Solution $solution): RedirectResponse
    {
        $data = $this->validated($request, $solution);
        $data['slug'] = $data['slug'] ?: Str::slug($data['name']);

        if ($request->hasFile('image')) {
            if ($solution->image) {
                Storage::disk('public')->delete($solution->image);
            }
            $data['image'] = $request->file('image')->store('solutions', 'public');
        }

        $solution->update($data);
        $solution->products()->sync($request->input('product_ids', []));

        return redirect()->route('admin.solutions.index')->with('status', 'Solution updated.');
    }

    public function destroy(Solution $solution): RedirectResponse
    {
        if ($solution->image) {
            Storage::disk('public')->delete($solution->image);
        }

        $solution->delete();

        return redirect()->route('admin.solutions.index')->with('status', 'Solution deleted.');
    }

    protected function validated(Request $request, ?Solution $solution = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'short_description' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string'],
            'image' => ['nullable', 'image', 'max:2048'],
            'status' => ['boolean'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
        ]);
    }
}
