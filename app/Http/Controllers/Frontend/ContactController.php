<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Http\Requests\Frontend\StoreContactRequest;
use App\Mail\NewEnquiryReceived;
use App\Models\Enquiry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

/**
 * General contact messages reuse the `enquiries` table (no items, no
 * customer match) rather than a separate contact_messages table — same
 * admin inbox, same status workflow, no duplicate schema for what is
 * essentially the same thing with zero products attached.
 */
class ContactController extends Controller
{
    public function create(): View
    {
        return view('frontend.contact.create');
    }

    public function store(StoreContactRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $enquiry = Enquiry::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? '',
            'message' => $data['message'],
            'status' => 'new',
        ]);

        $adminEmail = config('mail.admin_address');
        if ($adminEmail) {
            Mail::to($adminEmail)->queue(new NewEnquiryReceived($enquiry));
        }

        return redirect()->route('contact.create')->with('status', 'Thanks — we\'ll get back to you shortly.');
    }
}
