<?php

use App\Http\Controllers\Frontend\BrandController;
use App\Http\Controllers\Frontend\CategoryController;
use App\Http\Controllers\Frontend\HomeController;
use App\Http\Controllers\Frontend\ProductController;
use App\Http\Controllers\Frontend\SearchController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Frontend Routes
|--------------------------------------------------------------------------
| Everything below is public, no auth. Admin routes live in routes/admin.php
| and are required in at the bottom of this file — keep that require
| statement when you copy this in, or your /admin/* routes disappear.
*/

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/products', [ProductController::class, 'index'])->name('products.index');
Route::get('/products/{product:slug}', [ProductController::class, 'show'])->name('products.show');

Route::get('/categories/{category:slug}', [CategoryController::class, 'show'])->name('categories.show');
Route::get('/brands/{brand:slug}', [BrandController::class, 'show'])->name('brands.show');

Route::get('/search', [SearchController::class, 'index'])->name('search');

// Phase 5+ will add: /solutions/{slug}, /blog, /contact, /enquiry, /request-quote

require __DIR__.'/admin.php';
