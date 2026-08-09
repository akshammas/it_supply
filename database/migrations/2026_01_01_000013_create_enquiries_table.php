<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('enquiries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('company_name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone');
            $table->text('message')->nullable();
            $table->string('delivery_location')->nullable();
            $table->date('required_delivery_date')->nullable();
            $table->enum('status', [
                'new', 'contacted', 'quotation_sent', 'negotiating', 'won', 'lost', 'cancelled',
            ])->default('new');
            $table->timestamps();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('enquiries');
    }
};
