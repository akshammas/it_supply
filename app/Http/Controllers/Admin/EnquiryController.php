<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Enquiry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EnquiryController extends Controller
{
    protected array $statuses = [
        'new', 'contacted', 'quotation_sent', 'negotiating', 'won', 'lost', 'cancelled',
    ];

    public function index(Request $request): View
    {
        $enquiries = Enquiry::with('customer')
            ->withCount('items')
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.enquiries.index', [
            'enquiries' => $enquiries,
            'statuses' => $this->statuses,
        ]);
    }

    public function show(Enquiry $enquiry): View
    {
        $enquiry->load(['items.product', 'customer', 'quote']);

        return view('admin.enquiries.show', [
            'enquiry' => $enquiry,
            'statuses' => $this->statuses,
        ]);
    }

    public function updateStatus(Request $request, Enquiry $enquiry): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'in:'.implode(',', $this->statuses)],
        ]);

        $enquiry->update($data);

        return back()->with('status', 'Enquiry status updated.');
    }
}
