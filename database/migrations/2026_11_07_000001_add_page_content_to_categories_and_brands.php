<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // HTML block shown below the product cards on category / brand pages.
        Schema::table('categories', function (Blueprint $table) {
            $table->longText('seo_content')->nullable()->after('description');
        });
        Schema::table('brands', function (Blueprint $table) {
            $table->longText('seo_content')->nullable()->after('description');
        });

        // FAQs for brand pages (category pages reuse the existing category_faq pivot).
        Schema::create('brand_faq', function (Blueprint $table) {
            $table->foreignId('faq_id')->constrained()->cascadeOnDelete();
            $table->foreignId('brand_id')->constrained()->cascadeOnDelete();
            $table->primary(['faq_id', 'brand_id']);
        });

        // Blog posts chosen for category / brand pages.
        Schema::create('category_blog', function (Blueprint $table) {
            $table->foreignId('blog_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->primary(['blog_id', 'category_id']);
        });
        Schema::create('brand_blog', function (Blueprint $table) {
            $table->foreignId('blog_id')->constrained()->cascadeOnDelete();
            $table->foreignId('brand_id')->constrained()->cascadeOnDelete();
            $table->primary(['blog_id', 'brand_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('brand_blog');
        Schema::dropIfExists('category_blog');
        Schema::dropIfExists('brand_faq');

        Schema::table('brands', function (Blueprint $table) {
            $table->dropColumn('seo_content');
        });
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn('seo_content');
        });
    }
};
