<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pigs', function (Blueprint $table) {
            $table->foreignId('mother_sow_id')
                ->nullable()
                ->after('pig_source')
                ->constrained('pigs')
                ->nullOnDelete();

            $table->foreignId('reproduction_cycle_id')
                ->nullable()
                ->after('mother_sow_id')
                ->constrained('reproduction_cycles')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('pigs', function (Blueprint $table) {
            $table->dropConstrainedForeignId('reproduction_cycle_id');
            $table->dropConstrainedForeignId('mother_sow_id');
        });
    }
};
