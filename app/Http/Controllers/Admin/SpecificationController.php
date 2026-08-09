<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Specification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SpecificationController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'specification_group_id' => ['required', 'exists:specification_groups,id'],
            'name' => ['required', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        Specification::create($data);

        return back()->with('status', 'Specification added.');
    }

    public function update(Request $request, Specification $specification): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $specification->update($data);

        return back()->with('status', 'Specification updated.');
    }

    public function destroy(Specification $specification): RedirectResponse
    {
        if ($specification->productSpecifications()->exists()) {
            return back()->withErrors('This specification is in use on one or more products and cannot be deleted.');
        }

        $specification->delete();

        return back()->with('status', 'Specification deleted.');
    }
}
