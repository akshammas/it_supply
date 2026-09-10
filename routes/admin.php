<?php

use App\Http\Controllers\Admin\Auth\AdminAuthenticatedSessionController;
use App\Http\Controllers\Admin\BannerController;
use App\Http\Controllers\Admin\BlogCategoryController;
use App\Http\Controllers\Admin\BlogController;
use App\Http\Controllers\Admin\BrandController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\EnquiryController;
use App\Http\Controllers\Admin\ImportController;
use App\Http\Controllers\Admin\PageController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\QuoteController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\SolutionController;
use App\Http\Controllers\Admin\SpecificationController;
use App\Http\Controllers\Admin\SpecificationGroupController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
| Full replacement for the Phase 7 admin.php — adds CSV Import (Phase 8).
| Everything from earlier phases is unchanged.
*/

Route::prefix('admin')->name('admin.')->group(function () {

    Route::middleware('guest')->group(function () {
        Route::get('login', [AdminAuthenticatedSessionController::class, 'create'])
            ->name('login');

        Route::post('login', [AdminAuthenticatedSessionController::class, 'store'])
            ->middleware('throttle:5,1');
    });

    Route::middleware(['auth', 'admin'])->group(function () {
        Route::post('logout', [AdminAuthenticatedSessionController::class, 'destroy'])
            ->name('logout');

        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('dashboard', [DashboardController::class, 'index']);

        Route::resource('categories', CategoryController::class)->except('show');
        Route::resource('brands', BrandController::class)->except('show');

        Route::get('specifications', [SpecificationGroupController::class, 'index'])->name('specifications.index');
        Route::post('specification-groups', [SpecificationGroupController::class, 'store'])->name('specification-groups.store');
        Route::put('specification-groups/{specificationGroup}', [SpecificationGroupController::class, 'update'])->name('specification-groups.update');
        Route::delete('specification-groups/{specificationGroup}', [SpecificationGroupController::class, 'destroy'])->name('specification-groups.destroy');
        Route::post('specifications', [SpecificationController::class, 'store'])->name('specifications.store');
        Route::put('specifications/{specification}', [SpecificationController::class, 'update'])->name('specifications.update');
        Route::delete('specifications/{specification}', [SpecificationController::class, 'destroy'])->name('specifications.destroy');

        Route::resource('products', ProductController::class)->except('show');
        Route::post('products/bulk', [ProductController::class, 'bulk'])->name('products.bulk');
        Route::get('products-export', [ProductController::class, 'export'])->name('products.export');
        Route::post('products/{product}/duplicate', [ProductController::class, 'duplicate'])->name('products.duplicate');
        Route::delete('products/{product}/images/{image}', [ProductController::class, 'deleteImage'])->name('products.images.destroy');
        Route::post('products/{product}/images/{image}/primary', [ProductController::class, 'setPrimaryImage'])->name('products.images.primary');

        Route::get('imports', [ImportController::class, 'index'])->name('imports.index');
        Route::get('imports/create', [ImportController::class, 'create'])->name('imports.create');
        Route::post('imports/preview', [ImportController::class, 'preview'])->name('imports.preview');
        Route::post('imports', [ImportController::class, 'store'])->name('imports.store');
        Route::get('imports/{import}', [ImportController::class, 'show'])->name('imports.show');
        Route::get('imports/{import}/errors', [ImportController::class, 'downloadErrors'])->name('imports.errors');

        Route::get('enquiries', [EnquiryController::class, 'index'])->name('enquiries.index');
        Route::get('enquiries/{enquiry}', [EnquiryController::class, 'show'])->name('enquiries.show');
        Route::put('enquiries/{enquiry}/status', [EnquiryController::class, 'updateStatus'])->name('enquiries.status');
        Route::post('enquiries/{enquiry}/create-quote', [QuoteController::class, 'createFromEnquiry'])->name('quotes.create-from-enquiry');

        Route::get('quotes', [QuoteController::class, 'index'])->name('quotes.index');
        Route::get('quotes/{quote}', [QuoteController::class, 'show'])->name('quotes.show');
        Route::get('quotes/{quote}/edit', [QuoteController::class, 'edit'])->name('quotes.edit');
        Route::put('quotes/{quote}', [QuoteController::class, 'update'])->name('quotes.update');
        Route::put('quotes/{quote}/status', [QuoteController::class, 'updateStatus'])->name('quotes.status');
        Route::get('quotes/{quote}/pdf', [QuoteController::class, 'pdf'])->name('quotes.pdf');

        Route::resource('solutions', SolutionController::class)->except('show');

        Route::resource('blog', BlogController::class)->except('show')->parameters(['blog' => 'post']);
        Route::post('blog-categories', [BlogCategoryController::class, 'store'])->name('blog-categories.store');
        Route::delete('blog-categories/{blogCategory}', [BlogCategoryController::class, 'destroy'])->name('blog-categories.destroy');

        Route::resource('pages', PageController::class)->except('show');
        Route::resource('banners', BannerController::class)->except('show');

        Route::get('settings', [SettingController::class, 'edit'])->name('settings.edit');
        Route::put('settings', [SettingController::class, 'update'])->name('settings.update');

        // Still open: customers list, users management, 2FA.
    });
});
