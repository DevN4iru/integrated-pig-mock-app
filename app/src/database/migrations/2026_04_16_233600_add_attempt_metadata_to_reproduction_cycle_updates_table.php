<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reproduction_cycle_updates', function (Blueprint $table) {
            if (!Schema::hasColumn('reproduction_cycle_updates', 'attempt_number')) {
                $table->unsignedInteger('attempt_number')->default(1)->after('reproduction_cycle_id');
            }

            if (!Schema::hasColumn('reproduction_cycle_updates', 'boar_id')) {
                $table->foreignId('boar_id')->nullable()->after('attempt_number')->constrained('pigs')->nullOnDelete();
            }

            if (!Schema::hasColumn('reproduction_cycle_updates', 'breeding_type')) {
                $table->string('breeding_type', 50)->nullable()->after('boar_id');
            }

            if (!Schema::hasColumn('reproduction_cycle_updates', 'semen_source_type')) {
                $table->string('semen_source_type', 30)->nullable()->after('breeding_type');
            }

            if (!Schema::hasColumn('reproduction_cycle_updates', 'semen_source_name')) {
                $table->string('semen_source_name')->nullable()->after('semen_source_type');
            }

            if (!Schema::hasColumn('reproduction_cycle_updates', 'semen_cost')) {
                $table->decimal('semen_cost', 10, 2)->default(0)->after('semen_source_name');
            }
        });

        DB::table('reproduction_cycle_updates')
            ->whereNull('attempt_number')
            ->update([
                'attempt_number' => 1,
            ]);

        DB::statement(<<<'SQL'
            UPDATE reproduction_cycle_updates updates
            INNER JOIN reproduction_cycles cycles ON cycles.id = updates.reproduction_cycle_id
            SET
                updates.boar_id = cycles.boar_id,
                updates.breeding_type = cycles.breeding_type,
                updates.semen_source_type = cycles.semen_source_type,
                updates.semen_source_name = cycles.semen_source_name,
                updates.semen_cost = cycles.semen_cost,
                updates.attempt_number = 1
            WHERE updates.event_type = 'service_started'
        SQL);
    }

    public function down(): void
    {
        Schema::table('reproduction_cycle_updates', function (Blueprint $table) {
            if (Schema::hasColumn('reproduction_cycle_updates', 'boar_id')) {
                $table->dropConstrainedForeignId('boar_id');
            }

            foreach (['attempt_number', 'breeding_type', 'semen_source_type', 'semen_source_name', 'semen_cost'] as $column) {
                if (Schema::hasColumn('reproduction_cycle_updates', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
