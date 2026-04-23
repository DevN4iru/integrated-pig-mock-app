<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();

            $table->string('type_code', 100);
            $table->string('severity', 32);
            $table->string('title');
            $table->text('message');

            $table->string('route_name')->nullable();
            $table->json('route_params_json')->nullable();

            $table->foreignId('pig_id')
                ->nullable()
                ->constrained('pigs')
                ->nullOnDelete();

            $table->foreignId('reproduction_cycle_id')
                ->nullable()
                ->constrained('reproduction_cycles')
                ->nullOnDelete();

            $table->date('due_date')->nullable();
            $table->json('context_json')->nullable();

            $table->string('fingerprint')->unique();

            $table->timestamp('read_at')->nullable();
            $table->timestamp('dismissed_at')->nullable();
            $table->timestamp('resolved_at')->nullable();

            $table->timestamps();

            $table->index(['type_code', 'severity']);
            $table->index('due_date');
            $table->index('pig_id');
            $table->index('reproduction_cycle_id');
            $table->index('read_at');
            $table->index('dismissed_at');
            $table->index('resolved_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
