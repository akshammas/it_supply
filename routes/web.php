<?php

use App\Http\Controllers\Frontend\BrandController;
use App\Http\Controllers\Frontend\CategoryController;
use App\Http\Controllers\Frontend\ContactController;
use App\Http\Controllers\Frontend\EnquiryCartController;
use App\Http\Controllers\Frontend\HomeController;
use App\Http\Controllers\Frontend\ProductController;
use App\Http\Controllers\Frontend\QuoteRequestController;
use App\Http\Controllers\Frontend\SearchController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Frontend Routes
|--------------------------------------------------------------------------
| Full replacement for the Phase 4 web.php — adds the enquiry cart,
| request-quote flow, and contact form. Still requires admin.php at the
| bottom, same as before.
*/

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/products', [ProductController::class, 'index'])->name('products.index');
Route::get('/products/{product:slug}', [ProductController::class, 'show'])->name('products.show');

Route::get('/categories/{category:slug}', [CategoryController::class, 'show'])->name('categories.show');
Route::get('/brands/{brand:slug}', [BrandController::class, 'show'])->name('brands.show');

Route::get('/search', [SearchController::class, 'index'])->name('search');

// Section 16: session-based enquiry cart
Route::get('/enquiry', [EnquiryCartController::class, 'index'])->name('enquiry.index');
Route::post('/enquiry/add/{product}', [EnquiryCartController::class, 'add'])->name('enquiry.add');
Route::post('/enquiry/update/{product}', [EnquiryCartController::class, 'update'])->name('enquiry.update');
Route::delete('/enquiry/remove/{product}', [EnquiryCartController::class, 'remove'])->name('enquiry.remove');

// Section 17: Request Quote
Route::get('/request-quote', [QuoteRequestController::class, 'create'])->name('quote-request.create');
Route::post('/request-quote', [QuoteRequestController::class, 'store'])->name('quote-request.store');
Route::get('/thank-you', [QuoteRequestController::class, 'thankYou'])->name('quote-request.thank-you');

Route::get('/contact', [ContactController::class, 'create'])->name('contact.create');
Route::post('/contact', [ContactController::class, 'store'])->name('contact.store');

// Phase 6+ will add: /solutions/{slug}, /blog

require __DIR__.'/admin.php';
