<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reproduction_cycles', function (Blueprint $table) {
            $table->id();

            $table->foreignId('sow_id')
                ->constrained('pigs')
                ->cascadeOnDelete();

            $table->foreignId('boar_id')
                ->nullable()
                ->constrained('pigs')
                ->nullOnDelete();

            $table->string('breeding_type', 50);
            $table->date('service_date');
            $table->date('pregnancy_check_date')->nullable();
            $table->date('expected_farrow_date')->nullable();
            $table->date('actual_farrow_date')->nullable();

            $table->string('status', 30)->default('open');

            $table->string('semen_source_type', 30)->nullable();
            $table->string('semen_source_name')->nullable();
            $table->decimal('semen_cost', 10, 2)->default(0);
            $table->decimal('breeding_cost', 10, 2)->default(0);

            $table->unsignedInteger('total_born')->nullable();
            $table->unsignedInteger('born_alive')->nullable();
            $table->unsignedInteger('stillborn')->nullable();
            $table->unsignedInteger('mummified')->nullable();

            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['sow_id', 'service_date']);
            $table->index(['status', 'expected_farrow_date']);
            $table->index(['breeding_type', 'service_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reproduction_cycles');
    }
};
