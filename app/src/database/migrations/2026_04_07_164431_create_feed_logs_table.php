<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('feed_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pig_id')->constrained()->cascadeOnDelete();
            $table->string('feed_type');
            $table->date('start_feed_date');
            $table->date('end_feed_date')->nullable();
            $table->decimal('quantity', 10, 2);
            $table->string('unit');
            $table->string('feeding_time');
            $table->enum('status', ['ongoing', 'completed'])->default('ongoing');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feed_logs');
    }
};
