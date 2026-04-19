<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('pigs', 'age')) {
            Schema::table('pigs', function (Blueprint $table) {
                $table->unsignedInteger('age')->default(0)->after('pig_source');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('pigs', 'age')) {
            Schema::table('pigs', function (Blueprint $table) {
                $table->dropColumn('age');
            });
        }
    }
};