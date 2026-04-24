<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pig_transfers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pig_id')->constrained()->cascadeOnDelete();
            $table->foreignId('from_pen_id')->nullable()->constrained('pens')->nullOnDelete();
            $table->foreignId('to_pen_id')->constrained('pens')->restrictOnDelete();
            $table->date('transfer_date');
            $table->string('reason_code', 100);
            $table->text('reason_notes')->nullable();
            $table->timestamps();

            $table->index(['pig_id', 'transfer_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pig_transfers');
    }
};
