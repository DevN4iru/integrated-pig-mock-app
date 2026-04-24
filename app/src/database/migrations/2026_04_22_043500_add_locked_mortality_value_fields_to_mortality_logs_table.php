<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $hasWeightAtDeath = Schema::hasColumn('mortality_logs', 'weight_at_death');
        $hasPricePerKgAtDeath = Schema::hasColumn('mortality_logs', 'price_per_kg_at_death');
        $hasLossValue = Schema::hasColumn('mortality_logs', 'loss_value');

        if (!$hasWeightAtDeath || !$hasPricePerKgAtDeath || !$hasLossValue) {
            Schema::table('mortality_logs', function (Blueprint $table) use ($hasWeightAtDeath, $hasPricePerKgAtDeath, $hasLossValue) {
                if (!$hasWeightAtDeath) {
                    $table->decimal('weight_at_death', 10, 2)->nullable()->after('death_date');
                }

                if (!$hasPricePerKgAtDeath) {
                    $table->decimal('price_per_kg_at_death', 10, 2)->nullable()->after('weight_at_death');
                }

                if (!$hasLossValue) {
                    $table->decimal('loss_value', 10, 2)->nullable()->after('price_per_kg_at_death');
                }
            });
        }
    }

    public function down(): void
    {
        $hasWeightAtDeath = Schema::hasColumn('mortality_logs', 'weight_at_death');
        $hasPricePerKgAtDeath = Schema::hasColumn('mortality_logs', 'price_per_kg_at_death');
        $hasLossValue = Schema::hasColumn('mortality_logs', 'loss_value');

        if ($hasWeightAtDeath || $hasPricePerKgAtDeath || $hasLossValue) {
            Schema::table('mortality_logs', function (Blueprint $table) use ($hasWeightAtDeath, $hasPricePerKgAtDeath, $hasLossValue) {
                $dropColumns = [];

                if ($hasWeightAtDeath) {
                    $dropColumns[] = 'weight_at_death';
                }

                if ($hasPricePerKgAtDeath) {
                    $dropColumns[] = 'price_per_kg_at_death';
                }

                if ($hasLossValue) {
                    $dropColumns[] = 'loss_value';
                }

                if (!empty($dropColumns)) {
                    $table->dropColumn($dropColumns);
                }
            });
        }
    }
};
