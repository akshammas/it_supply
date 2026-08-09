<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SpecificationGroup;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Groups + their specifications are managed together on one screen
 * (section 10) rather than as two separate CRUD sections — admins think
 * in terms of "Server Specifications: Processor, Memory, Storage...",
 * not in terms of two unrelated tables.
 */
class SpecificationGroupController extends Controller
{
    public function index(): View
    {
        $groups = SpecificationGroup::with('specifications')->orderBy('sort_order')->get();

        return view('admin.specifications.index', compact('groups'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        SpecificationGroup::create($data);

        return back()->with('status', 'Specification group added.');
    }

    public function update(Request $request, SpecificationGroup $specificationGroup): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $specificationGroup->update($data);

        return back()->with('status', 'Specification group updated.');
    }

    public function destroy(SpecificationGroup $specificationGroup): RedirectResponse
    {
        if ($specificationGroup->specifications()->exists()) {
            return back()->withErrors('Remove or reassign the specifications in this group first.');
        }

        $specificationGroup->delete();

        return back()->with('status', 'Specification group deleted.');
    }
}
