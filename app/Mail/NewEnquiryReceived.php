<?php

namespace App\Mail;

use App\Models\Enquiry;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NewEnquiryReceived extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Enquiry $enquiry)
    {
    }

    public function build(): self
    {
        $from = $this->enquiry->company_name ?: $this->enquiry->name;

        return $this
            ->subject("New Enquiry #{$this->enquiry->id} — {$from}")
            ->view('emails.new-enquiry');
    }
}
