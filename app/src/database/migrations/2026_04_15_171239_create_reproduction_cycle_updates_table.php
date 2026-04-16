<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reproduction_cycle_updates', function (Blueprint $table) {
            $table->id();

            $table->foreignId('reproduction_cycle_id')
                ->constrained('reproduction_cycles')
                ->cascadeOnDelete();

            $table->string('event_type');
            $table->date('event_date');

            $table->string('status_after_event')->nullable();
            $table->string('pregnancy_result')->nullable();

            $table->date('actual_farrow_date')->nullable();

            $table->integer('total_born')->nullable();
            $table->integer('born_alive')->nullable();
            $table->integer('stillborn')->nullable();
            $table->integer('mummified')->nullable();

            $table->decimal('added_cost', 10, 2)->default(0);

            $table->text('notes')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reproduction_cycle_updates');
    }
};
