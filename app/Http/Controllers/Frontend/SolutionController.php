<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Solution;
use Illuminate\View\View;

class SolutionController extends Controller
{
    public function index(): View
    {
        $solutions = Solution::where('status', true)->paginate(12);

        return view('frontend.solutions.index', compact('solutions'));
    }

    public function show(Solution $solution): View
    {
        abort_unless($solution->status, 404);

        $products = $solution->products()->where('status', true)->with(['brand', 'primaryImage'])->get();

        return view('frontend.solutions.show', compact('solution', 'products'));
    }
}
