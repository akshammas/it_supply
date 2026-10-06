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
        return view('frontend.contact.create', [
            'enquiryTypes' => StoreContactRequest::ENQUIRY_TYPES,
        ]);
    }

    public function store(StoreContactRequest $request): RedirectResponse
    {
        $data = $request->validated();

        // The enquiries table has no "subject" column, so the enquiry type goes at the top of the message
        $message = $data['message'];
        if (! empty($data['enquiry_type'])) {
            $message = 'Enquiry type: '.$data['enquiry_type']."\n\n".$message;
        }

        $enquiry = Enquiry::create([
            'name' => $data['name'],
            'company_name' => $data['company_name'] ?? null,
            'email' => $data['email'],
            'phone' => $data['phone'] ?? '',
            'message' => $message,
            'status' => 'new',
        ]);

        $adminEmail = config('mail.admin_address');
        if ($adminEmail) {
            Mail::to($adminEmail)->queue(new NewEnquiryReceived($enquiry));
        }

        return redirect(route('contact.create').'#contact-form')
            ->with('status', 'Thanks — we\'ll get back to you shortly.');
    }
}