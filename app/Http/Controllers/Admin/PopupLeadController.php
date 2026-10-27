<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PopupLead;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PopupLeadController extends Controller
{
    public function index(Request $request): View
    {
        $leads = PopupLead::with('popup')
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = '%' . $request->q . '%';
                $q->where(fn ($w) => $w->where('name', 'like', $term)->orWhere('phone', 'like', $term));
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $newCount = PopupLead::where('status', 'new')->count();

        return view('admin.popup-leads.index', compact('leads', 'newCount'));
    }

    public function toggleStatus(PopupLead $popupLead): RedirectResponse
    {
        $popupLead->update(['status' => $popupLead->status === 'new' ? 'contacted' : 'new']);

        return back()->with('status', 'Lead marked as ' . $popupLead->status . '.');
    }

    public function destroy(PopupLead $popupLead): RedirectResponse
    {
        $popupLead->delete();

        return back()->with('status', 'Lead deleted.');
    }

    public function export(): StreamedResponse
    {
        return response()->streamDownload(function () {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Date', 'Name', 'Phone', 'Popup', 'Page', 'Status']);
            PopupLead::with('popup')->latest()->chunk(200, function ($rows) use ($out) {
                foreach ($rows as $l) {
                    fputcsv($out, [$l->created_at->format('Y-m-d H:i'), $l->name, $l->phone, $l->popup?->name, $l->page_url, $l->status]);
                }
            });
            fclose($out);
        }, 'popup-leads-' . now()->format('Y-m-d') . '.csv', ['Content-Type' => 'text/csv']);
    }
}