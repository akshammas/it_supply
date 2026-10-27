<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('popups', function (Blueprint $table) {
            $table->id();
            $table->string('name');                                   // internal label
            $table->string('layout', 20)->default('standard');        // standard | image_only
            $table->string('title')->nullable();
            $table->text('body')->nullable();
            $table->string('image')->nullable();
            $table->string('button_text', 100)->nullable();
            $table->string('button_url', 500)->nullable();
            $table->boolean('button_new_tab')->default(false);

            $table->string('trigger', 20)->default('delay');          // delay | scroll | exit_intent
            $table->unsignedSmallInteger('trigger_delay')->default(3);   // seconds
            $table->unsignedTinyInteger('trigger_scroll')->default(50);  // percent
            $table->string('frequency', 20)->default('once_per_session');
            $table->string('show_on', 20)->default('home');           // all | home | products | categories | custom
            $table->text('custom_paths')->nullable();                 // one per line
            $table->string('device', 10)->default('all');             // all | desktop | mobile

            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->boolean('status')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('popups');
    }
};
