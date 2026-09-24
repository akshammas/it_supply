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

        // A blank sort_order field validates as null, but an explicit
        // NULL sent to the database overrides the column's DEFAULT 0 —
        // MySQL only applies a column default when it's omitted from the
        // INSERT entirely, not when NULL is passed on purpose.
        $data['sort_order'] ??= 0;

        SpecificationGroup::create($data);

        return back()->with('status', 'Specification group added.');
    }

    public function update(Request $request, SpecificationGroup $specificationGroup): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $data['sort_order'] ??= 0;

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