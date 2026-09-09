<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Http\Requests\Frontend\StoreQuoteRequestRequest;
use App\Mail\NewEnquiryReceived;
use App\Models\Customer;
use App\Models\Enquiry;
use App\Services\EnquiryCartService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class QuoteRequestController extends Controller
{
    public function __construct(protected EnquiryCartService $cart)
    {
    }

    public function create(): View|RedirectResponse
    {
        if ($this->cart->isEmpty()) {
            return redirect()->route('products.index')
                ->with('status', 'Add a product to your enquiry list first, then request a quote.');
        }

        return view('frontend.quote-request.create', [
            'items' => $this->cart->items(),
        ]);
    }

    public function store(StoreQuoteRequestRequest $request): RedirectResponse
    {
        if ($this->cart->isEmpty()) {
            return redirect()->route('products.index')->with('status', 'Your enquiry list is empty.');
        }

        $data = $request->validated();

        $enquiry = DB::transaction(function () use ($data) {
            // Match an existing customer by email so repeat enquiries
            // build a history (section 18 admin view benefits from this),
            // rather than creating a fresh customer row every time.
            $customer = Customer::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'company_name' => $data['company_name'] ?? null,
                    'phone' => $data['phone'],
                    'trn' => $data['trn'] ?? null,
                    'emirate' => $data['emirate'] ?? null,
                ]
            );

            $enquiry = Enquiry::create([
                'customer_id' => $customer->id,
                'name' => $data['name'],
                'company_name' => $data['company_name'] ?? null,
                'email' => $data['email'],
                'phone' => $data['phone'],
                'message' => $data['message'] ?? null,
                'delivery_location' => $data['delivery_location'] ?? null,
                'required_delivery_date' => $data['required_delivery_date'] ?? null,
                'status' => 'new',
            ]);

            foreach ($this->cart->items() as $item) {
                $enquiry->items()->create([
                    'product_id' => $item['product']->id,
                    'quantity' => $item['quantity'],
                ]);
            }

            return $enquiry;
        });

        $this->cart->clear();

        $adminEmail = config('mail.admin_address');
        if ($adminEmail) {
            Mail::to($adminEmail)->queue(new NewEnquiryReceived($enquiry->load('items.product')));
        }

        return redirect()->route('quote-request.thank-you');
    }

    public function thankYou(): View
    {
        return view('frontend.quote-request.thank-you');
    }
}
