<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\View\View;

class BrandController extends Controller
{
    public function show(Brand $brand): View
    {
        abort_unless($brand->status, 404);

        $products = $brand->products()
            ->with(['category', 'primaryImage'])
            ->where('status', true)
            ->latest()
            ->paginate(24);

        return view('frontend.brands.show', compact('brand', 'products'));
    }
}
