<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $hasMotherSowId = Schema::hasColumn('pigs', 'mother_sow_id');
        $hasReproductionCycleId = Schema::hasColumn('pigs', 'reproduction_cycle_id');

        if (!$hasMotherSowId || !$hasReproductionCycleId) {
            Schema::table('pigs', function (Blueprint $table) use ($hasMotherSowId, $hasReproductionCycleId) {
                if (!$hasMotherSowId) {
                    $table->foreignId('mother_sow_id')
                        ->nullable()
                        ->after('pig_source')
                        ->constrained('pigs')
                        ->nullOnDelete();
                }

                if (!$hasReproductionCycleId) {
                    $table->foreignId('reproduction_cycle_id')
                        ->nullable()
                        ->after('mother_sow_id')
                        ->constrained('reproduction_cycles')
                        ->nullOnDelete();
                }
            });
        }
    }

    public function down(): void
    {
        Schema::table('pigs', function (Blueprint $table) {
            if (Schema::hasColumn('pigs', 'reproduction_cycle_id')) {
                $table->dropConstrainedForeignId('reproduction_cycle_id');
            }

            if (Schema::hasColumn('pigs', 'mother_sow_id')) {
                $table->dropConstrainedForeignId('mother_sow_id');
            }
        });
    }
};
