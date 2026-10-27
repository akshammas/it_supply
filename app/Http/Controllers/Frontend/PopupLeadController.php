<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Popup;
use App\Models\PopupLead;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class PopupLeadController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        // Honeypot: real visitors never fill this hidden field
        if ($request->filled('website')) {
            return response()->json(['message' => 'Thank you!']);
        }

        $popup = Popup::where('show_form', true)->find($request->input('popup_id'));
        abort_unless($popup, 404);

        $data = $request->validate([
            'name'    => ['required', 'string', 'max:100'],
            'phone'   => ['required', 'string', 'max:30', 'regex:/^[0-9+\-\s()]{7,20}$/'],
            'consent' => [$popup->consent_required ? 'accepted' : 'nullable'],
        ], [
            'name.required'    => 'Please enter your name.',
            'phone.required'   => 'Please enter your phone number.',
            'phone.regex'      => 'Please enter a valid phone number.',
            'consent.accepted' => 'Please tick the box to continue.',
        ]);

        $lead = PopupLead::create([
            'popup_id'   => $popup->id,
            'name'       => $data['name'],
            'phone'      => $data['phone'],
            'page_url'   => Str::limit((string) $request->input('page_url'), 500, ''),
            'consented'  => $request->boolean('consent'),
            'status'     => 'new',
            'ip_address' => $request->ip(),
        ]);

        // Optional heads-up email to the admin (never blocks the visitor)
        try {
            if ($to = config('mail.admin_address')) {
                Mail::raw(
                    "New call-back request\n\nName: {$lead->name}\nPhone: {$lead->phone}\nPopup: {$popup->name}\nPage: {$lead->page_url}",
                    fn ($m) => $m->to($to)->subject("Call-back request - {$lead->name}")
                );
            }
        } catch (\Throwable $e) {
            report($e);
        }

        return response()->json([
            'message' => $popup->success_message ?: 'Thank you! We will get back to you shortly.',
        ]);
    }
}