<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\EnquiryCartService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EnquiryCartController extends Controller
{
    public function __construct(protected EnquiryCartService $cart)
    {
    }

    public function index(): View
    {
        return view('frontend.enquiry.index', [
            'items' => $this->cart->items(),
            'whatsappMessage' => $this->cart->whatsappMessage(),
        ]);
    }

    public function add(Request $request, Product $product): RedirectResponse
    {
        abort_unless($product->status, 404);

        $quantity = (int) $request->input('quantity', 1);
        $this->cart->add($product->id, $quantity);

        if ($request->boolean('redirect_to_quote')) {
            return redirect()->route('quote-request.create');
        }

        return back()->with('status', "{$product->name} added to your enquiry list.");
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $quantity = (int) $request->input('quantity', 1);
        $this->cart->update($product->id, $quantity);

        return back()->with('status', 'Enquiry list updated.');
    }

    public function remove(Product $product): RedirectResponse
    {
        $this->cart->remove($product->id);

        return back()->with('status', 'Item removed from your enquiry list.');
    }
}
