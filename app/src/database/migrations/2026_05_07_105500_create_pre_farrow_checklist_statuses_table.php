<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('pre_farrow_checklist_statuses')) {
            return;
        }

        Schema::create('pre_farrow_checklist_statuses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reproduction_cycle_id')->constrained()->cascadeOnDelete();
            $table->string('checklist_key');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->unique(['reproduction_cycle_id', 'checklist_key'], 'pre_farrow_status_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pre_farrow_checklist_statuses');
    }
};
