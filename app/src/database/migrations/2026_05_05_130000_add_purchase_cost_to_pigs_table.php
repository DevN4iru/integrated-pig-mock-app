<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('pigs', 'purchase_cost')) {
            Schema::table('pigs', function (Blueprint $table) {
                $table->decimal('purchase_cost', 10, 2)->default(0)->after('pig_source');
            });
        }

        DB::table('pigs')
            ->where('pig_source', 'purchased')
            ->where(function ($query) {
                $query->whereNull('purchase_cost')->orWhere('purchase_cost', 0);
            })
            ->update(['purchase_cost' => DB::raw('asset_value')]);
    }

    public function down(): void
    {
        if (Schema::hasColumn('pigs', 'purchase_cost')) {
            Schema::table('pigs', function (Blueprint $table) {
                $table->dropColumn('purchase_cost');
            });
        }
    }
};
