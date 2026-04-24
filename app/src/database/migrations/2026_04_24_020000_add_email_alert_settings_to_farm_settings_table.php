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
        Schema::table('farm_settings', function (Blueprint $table) {
            $table->string('alert_recipient_email')->nullable()->after('price_per_kg');
            $table->time('server_close_reminder_time')->nullable()->after('alert_recipient_email');
            $table->time('feed_reminder_time')->nullable()->after('server_close_reminder_time');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('farm_settings', function (Blueprint $table) {
            $table->dropColumn([
                'alert_recipient_email',
                'server_close_reminder_time',
                'feed_reminder_time',
            ]);
        });
    }
};
