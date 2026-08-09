<?php

use App\Http\Controllers\Admin\Auth\AdminAuthenticatedSessionController;
use App\Http\Controllers\Admin\BrandController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\SpecificationController;
use App\Http\Controllers\Admin\SpecificationGroupController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
| Full replacement for the Phase 2 admin.php — includes the Phase 2 auth
| routes plus Phase 3's catalogue CRUD (Products, Categories, Brands,
| Specifications). Require this file from routes/web.php:
|   require __DIR__.'/admin.php';
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

        // Phase 4+ will add: enquiries, quotes, customers, solutions,
        // blog, pages, banners, users, settings, CSV import.
    });
});
