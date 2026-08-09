<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('brand_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('sku')->unique();
            $table->string('model_number')->nullable();
            $table->text('short_description')->nullable();
            $table->longText('description')->nullable();
            $table->decimal('price', 12, 2)->nullable();
            $table->decimal('sale_price', 12, 2)->nullable();
            $table->enum('price_type', ['fixed', 'sale', 'on_request'])->default('on_request');
            $table->integer('stock')->default(0);
            $table->enum('stock_status', ['in_stock', 'out_of_stock', 'preorder'])->default('in_stock');
            $table->enum('condition', ['new', 'refurbished', 'used'])->default('new');
            $table->boolean('featured')->default(false);
            $table->boolean('status')->default(true);
            $table->string('meta_title')->nullable();
            $table->string('meta_description')->nullable();
            $table->timestamps();

            $table->index(['category_id', 'status']);
            $table->index(['brand_id', 'status']);
            $table->index('featured');
            $table->fullText(['name', 'sku', 'model_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
