<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('feed_logs', function (Blueprint $table) {
            $table->decimal('cost', 12, 2)->default(0)->after('quantity');
        });

        Schema::table('medications', function (Blueprint $table) {
            $table->decimal('cost', 12, 2)->default(0)->after('dosage');
        });

        Schema::table('vaccinations', function (Blueprint $table) {
            $table->decimal('cost', 12, 2)->default(0)->after('dose');
        });
    }

    public function down(): void
    {
        Schema::table('vaccinations', function (Blueprint $table) {
            $table->dropColumn('cost');
        });

        Schema::table('medications', function (Blueprint $table) {
            $table->dropColumn('cost');
        });

        Schema::table('feed_logs', function (Blueprint $table) {
            $table->dropColumn('cost');
        });
    }
};
