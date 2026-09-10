<?php

use App\Http\Controllers\Frontend\BlogController;
use App\Http\Controllers\Frontend\BrandController;
use App\Http\Controllers\Frontend\CategoryController;
use App\Http\Controllers\Frontend\ContactController;
use App\Http\Controllers\Frontend\EnquiryCartController;
use App\Http\Controllers\Frontend\HomeController;
use App\Http\Controllers\Frontend\PageController;
use App\Http\Controllers\Frontend\ProductController;
use App\Http\Controllers\Frontend\QuoteRequestController;
use App\Http\Controllers\Frontend\SearchController;
use App\Http\Controllers\Frontend\SolutionController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Frontend Routes
|--------------------------------------------------------------------------
| Full replacement for the Phase 5 web.php — adds Solutions, Blog, Pages
| (Phase 7). IMPORTANT: the {page:slug} catch-all at the very bottom of
| this file MUST stay last, after admin.php is required — otherwise a
| visit to /admin would get swallowed as if "admin" were a page slug.
| Never insert new routes below that line without moving it back down.
*/

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/products', [ProductController::class, 'index'])->name('products.index');
Route::get('/products/{product:slug}', [ProductController::class, 'show'])->name('products.show');

Route::get('/categories/{category:slug}', [CategoryController::class, 'show'])->name('categories.show');
Route::get('/brands/{brand:slug}', [BrandController::class, 'show'])->name('brands.show');

Route::get('/search', [SearchController::class, 'index'])->name('search');

Route::get('/enquiry', [EnquiryCartController::class, 'index'])->name('enquiry.index');
Route::post('/enquiry/add/{product}', [EnquiryCartController::class, 'add'])->name('enquiry.add');
Route::post('/enquiry/update/{product}', [EnquiryCartController::class, 'update'])->name('enquiry.update');
Route::delete('/enquiry/remove/{product}', [EnquiryCartController::class, 'remove'])->name('enquiry.remove');

Route::get('/request-quote', [QuoteRequestController::class, 'create'])->name('quote-request.create');
Route::post('/request-quote', [QuoteRequestController::class, 'store'])->name('quote-request.store');
Route::get('/thank-you', [QuoteRequestController::class, 'thankYou'])->name('quote-request.thank-you');

Route::get('/contact', [ContactController::class, 'create'])->name('contact.create');
Route::post('/contact', [ContactController::class, 'store'])->name('contact.store');

Route::get('/solutions', [SolutionController::class, 'index'])->name('solutions.index');
Route::get('/solutions/{solution:slug}', [SolutionController::class, 'show'])->name('solutions.show');

Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');
Route::get('/blog/{post:slug}', [BlogController::class, 'show'])->name('blog.show');

require __DIR__.'/admin.php';

// CATCH-ALL — must stay last. Matches any remaining single-segment URL
// (e.g. /about-us, /privacy-policy, /warranty) against a published Page.
// If no Page with that slug exists, Laravel's route model binding 404s
// naturally — nothing above this line is at risk of being swallowed by it.
Route::get('/{page:slug}', [PageController::class, 'show'])->name('pages.show');
