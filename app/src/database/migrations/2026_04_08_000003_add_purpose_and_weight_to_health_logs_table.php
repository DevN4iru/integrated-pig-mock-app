<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('health_logs', function (Blueprint $table) {
            $table->string('purpose')->default('observation')->after('pig_id');
            $table->decimal('weight', 8, 2)->nullable()->after('condition');
        });

        DB::table('health_logs')
            ->whereNull('purpose')
            ->update(['purpose' => 'observation']);
    }

    public function down(): void
    {
        Schema::table('health_logs', function (Blueprint $table) {
            $table->dropColumn(['purpose', 'weight']);
        });
    }
};