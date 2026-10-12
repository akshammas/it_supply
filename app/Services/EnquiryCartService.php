<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

/**
 * Section 16: a session-based "Enquiry Cart" — deliberately NOT written to
 * the database until the customer actually submits Request Quote. Keeps
 * casual browsing cheap (no orphaned Enquiry rows from people who never
 * finish) and mirrors how a normal e-commerce cart behaves, just without
 * checkout/payment.
 */
class EnquiryCartService
{
    protected string $sessionKey = 'enquiry_cart';

    public function add(int $productId, int $quantity = 1): void
    {
        $cart = $this->raw();
        $cart[$productId] = ($cart[$productId] ?? 0) + max(1, $quantity);
        Session::put($this->sessionKey, $cart);
    }

    public function update(int $productId, int $quantity): void
    {
        $cart = $this->raw();

        if ($quantity < 1) {
            unset($cart[$productId]);
        } else {
            $cart[$productId] = $quantity;
        }

        Session::put($this->sessionKey, $cart);
    }

    public function remove(int $productId): void
    {
        $cart = $this->raw();
        unset($cart[$productId]);
        Session::put($this->sessionKey, $cart);
    }

    public function clear(): void
    {
        Session::forget($this->sessionKey);
    }

    public function count(): int
    {
        return array_sum($this->raw());
    }

    /**
     * @return Collection<int, array{product: Product, quantity: int}>
     */
    public function items(): Collection
    {
        $cart = $this->raw();

        if (empty($cart)) {
            return collect();
        }

        return Product::with(['brand', 'primaryImage'])
            ->whereIn('id', array_keys($cart))
            ->where('status', true)
            ->get()
            ->map(fn (Product $product) => [
                'product' => $product,
                'quantity' => $cart[$product->id],
            ]);
    }

    public function isEmpty(): bool
    {
        return empty($this->raw());
    }

    /**
     * Plain-text summary used to build the aggregate WhatsApp message
     * (section 16: "Enquiry List ... [WhatsApp]").
     */
    public function whatsappMessage(): string
    {
        $lines = ["Hello,\n\nI am interested in the following products:\n"];

        foreach ($this->items() as $item) {
            $lines[] = "- {$item['product']->name} (SKU: {$item['product']->sku}) x{$item['quantity']}";
        }

        $lines[] = "\nPlease provide price and availability.\n\nThank you.";

        return implode("\n", $lines);
    }

    /**
     * Default subject for the quote form, e.g.
     * "Quote request: Lenovo 27-inch QHD Business Monitor"
     * or "Quote request: Lenovo 27-inch… + 2 more" for several products.
     */
    public function quoteSubject(): string
    {
        $items = $this->items();

        if ($items->isEmpty()) {
            return 'Quote request';
        }

        $first = Str::limit($items->first()['product']->name, 100, '…');

        return $items->count() === 1
            ? "Quote request: {$first}"
            : "Quote request: {$first} + " . ($items->count() - 1) . ' more';
    }

    /**
     * Pre-written message listing the product(s) the customer picked.
     */
    public function quoteMessage(): string
    {
        $lines = ["Hello,\n\nI would like to request a quote for the following:\n"];

        foreach ($this->items() as $item) {
            $sku = $item['product']->sku ? " (SKU: {$item['product']->sku})" : '';
            $lines[] = "- {$item['product']->name}{$sku} x{$item['quantity']}";
        }

        $lines[] = "\nPlease share your best price and availability.\n\nThank you.";

        return implode("\n", $lines);
    }


    protected function raw(): array
    {
        return Session::get($this->sessionKey, []);
    }
}
