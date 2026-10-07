<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminActivityLog;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Enquiry;
use App\Models\Product;
use App\Models\Quote;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'products' => Product::count(),
            'categories' => Category::count(),
            'brands' => Brand::count(),
            'enquiries' => Enquiry::count(),
            'new_enquiries' => Enquiry::where('status', 'new')->count(),
            'quotes' => Quote::count(),
        ];

        $recentEnquiries = Enquiry::withCount('items')
            ->latest()
            ->take(5)
            ->get();

        $recentActivity = AdminActivityLog::with('user')
            ->latest('created_at')
            ->take(8)
            ->get();

        // Enquiries per day for the last 7 days (today included)
        $perDay = Enquiry::where('created_at', '>=', now()->subDays(6)->startOfDay())
            ->selectRaw('DATE(created_at) as d, COUNT(*) as c')
            ->groupBy('d')
            ->pluck('c', 'd');

        $chart = collect(range(6, 0))->map(function ($i) use ($perDay) {
            $date = now()->subDays($i);
            return [
                'label' => $date->format('D'),
                'date'  => $date->format('d M'),
                'count' => (int) ($perDay[$date->toDateString()] ?? 0),
            ];
        });

        return view('admin.dashboard', compact('stats', 'recentEnquiries', 'recentActivity', 'chart'));
    }
}
