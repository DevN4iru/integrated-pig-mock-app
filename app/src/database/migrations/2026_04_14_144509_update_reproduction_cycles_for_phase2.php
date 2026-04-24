<?php

use App\Models\ReproductionCycle;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reproduction_cycles', function (Blueprint $table) {
            if (!Schema::hasColumn('reproduction_cycles', 'pregnancy_result')) {
                $table->string('pregnancy_result', 30)
                    ->default(ReproductionCycle::PREGNANCY_RESULT_PENDING)
                    ->after('pregnancy_check_date');
            }
        });

        DB::table('reproduction_cycles')
            ->where('status', 'open')
            ->update([
                'status' => ReproductionCycle::STATUS_SERVICED,
            ]);

        DB::table('reproduction_cycles')
            ->where('status', 'failed')
            ->update([
                'status' => ReproductionCycle::STATUS_NOT_PREGNANT,
            ]);

        DB::table('reproduction_cycles')
            ->whereNull('pregnancy_result')
            ->update([
                'pregnancy_result' => ReproductionCycle::PREGNANCY_RESULT_PENDING,
            ]);

        DB::table('reproduction_cycles')
            ->whereIn('status', [
                ReproductionCycle::STATUS_PREGNANT,
                ReproductionCycle::STATUS_DUE_SOON,
                ReproductionCycle::STATUS_FARROWED,
            ])
            ->update([
                'pregnancy_result' => ReproductionCycle::PREGNANCY_RESULT_PREGNANT,
            ]);

        DB::table('reproduction_cycles')
            ->whereIn('status', [
                ReproductionCycle::STATUS_NOT_PREGNANT,
                ReproductionCycle::STATUS_RETURNED_TO_HEAT,
            ])
            ->update([
                'pregnancy_result' => ReproductionCycle::PREGNANCY_RESULT_NOT_PREGNANT,
            ]);

        DB::table('reproduction_cycles')
            ->where('status', ReproductionCycle::STATUS_SERVICED)
            ->whereNotNull('actual_farrow_date')
            ->update([
                'status' => ReproductionCycle::STATUS_FARROWED,
                'pregnancy_result' => ReproductionCycle::PREGNANCY_RESULT_PREGNANT,
            ]);

        DB::table('reproduction_cycles')
            ->where('status', ReproductionCycle::STATUS_PREGNANT)
            ->whereNotNull('expected_farrow_date')
            ->whereBetween('expected_farrow_date', [
                now()->toDateString(),
                now()->copy()->addDays(ReproductionCycle::dueSoonThresholdDays())->toDateString(),
            ])
            ->update([
                'status' => ReproductionCycle::STATUS_DUE_SOON,
                'pregnancy_result' => ReproductionCycle::PREGNANCY_RESULT_PREGNANT,
            ]);

        Schema::table('reproduction_cycles', function (Blueprint $table) {
            $table->index(['pregnancy_result', 'pregnancy_check_date'], 'repro_cycles_pregnancy_result_check_idx');
        });
    }

    public function down(): void
    {
        Schema::table('reproduction_cycles', function (Blueprint $table) {
            $table->dropIndex('repro_cycles_pregnancy_result_check_idx');
        });

        DB::table('reproduction_cycles')
            ->where('status', ReproductionCycle::STATUS_SERVICED)
            ->update([
                'status' => 'open',
            ]);

        DB::table('reproduction_cycles')
            ->where('status', ReproductionCycle::STATUS_NOT_PREGNANT)
            ->update([
                'status' => 'failed',
            ]);

        DB::table('reproduction_cycles')
            ->where('status', ReproductionCycle::STATUS_RETURNED_TO_HEAT)
            ->update([
                'status' => 'failed',
            ]);

        Schema::table('reproduction_cycles', function (Blueprint $table) {
            if (Schema::hasColumn('reproduction_cycles', 'pregnancy_result')) {
                $table->dropColumn('pregnancy_result');
            }
        });
    }
};
