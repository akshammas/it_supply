<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('popups', function (Blueprint $table) {
            $table->boolean('show_form')->default(false)->after('button_new_tab');
            $table->string('form_button_text', 60)->default('Request a Call Back')->after('show_form');
            $table->string('success_message')->nullable()->after('form_button_text');
            $table->string('consent_text')->nullable()->after('success_message');
            $table->string('privacy_url')->nullable()->after('consent_text');
            $table->boolean('consent_required')->default(false)->after('privacy_url');
        });

        Schema::create('popup_leads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('popup_id')->nullable()->constrained('popups')->nullOnDelete();
            $table->string('name', 100);
            $table->string('phone', 30);
            $table->string('page_url', 500)->nullable();
            $table->boolean('consented')->default(false);
            $table->string('status', 20)->default('new'); // new | contacted
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('popup_leads');

        Schema::table('popups', function (Blueprint $table) {
            $table->dropColumn(['show_form', 'form_button_text', 'success_message', 'consent_text', 'privacy_url', 'consent_required']);
        });
    }
};