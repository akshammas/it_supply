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
use App\Http\Controllers\Frontend\SitemapController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Frontend Routes
|--------------------------------------------------------------------------
| Full replacement for the Phase 7b web.php — adds sitemap.xml and
| robots.txt (Phase 9). IMPORTANT: the {page:slug} catch-all at the very
| bottom of this file MUST stay last. sitemap.xml and robots.txt are
| registered ABOVE it for the same reason /admin needs to be — both are
| single-segment paths the catch-all would otherwise try to match as a
| Page slug first.
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


Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');
Route::get('/blog/{post:slug}', [BlogController::class, 'show'])->name('blog.show');

Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');
Route::get('/robots.txt', function () {
    $lines = [
        'User-agent: *',
        'Allow: /',
        'Disallow: /admin',
        'Disallow: /enquiry',
        'Disallow: /request-quote',
        'Sitemap: '.route('sitemap'),
    ];

    return response(implode("\n", $lines), 200)->header('Content-Type', 'text/plain');
})->name('robots');

require __DIR__.'/admin.php';

Route::view('/about-us', 'frontend.about')->name('about');

// CATCH-ALL — must stay last. See note at top of file.
Route::get('/{page:slug}', [PageController::class, 'show'])->name('pages.show');
