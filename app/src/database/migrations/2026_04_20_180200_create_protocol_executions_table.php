<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('protocol_executions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('pig_id')
                ->constrained('pigs')
                ->cascadeOnDelete();

            $table->foreignId('protocol_rule_id')
                ->constrained('protocol_rules')
                ->cascadeOnDelete();

            $table->date('scheduled_for_date');
            $table->string('status');
            $table->date('executed_date')->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index('status');
            $table->index(['pig_id', 'protocol_rule_id', 'scheduled_for_date'], 'protocol_exec_pig_rule_sched_idx');

            $table->unique(
                ['pig_id', 'protocol_rule_id', 'scheduled_for_date'],
                'protocol_exec_pig_rule_sched_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('protocol_executions');
    }
};
