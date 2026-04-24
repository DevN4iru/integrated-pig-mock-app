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
        Schema::create('email_alert_deliveries', function (Blueprint $table) {
            $table->id();

            $table->string('fingerprint')->unique();
            $table->string('alert_type', 100);
            $table->string('recipient');
            $table->string('subject');
            $table->string('status', 32)->default('pending');
            $table->json('payload_json')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->text('error_message')->nullable();

            $table->timestamps();

            $table->index('alert_type');
            $table->index('status');
            $table->index('sent_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('email_alert_deliveries');
    }
};
