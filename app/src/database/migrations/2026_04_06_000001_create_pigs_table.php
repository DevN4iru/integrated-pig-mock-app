<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('pigs', function (Blueprint $table) {
            $table->id();
            $table->string('ear_tag');
            $table->string('breed');
            $table->string('sex');
            $table->string('pen_location');
            $table->string('status');
            $table->date('origin_date');
            $table->decimal('latest_weight', 10, 2);
            $table->date('weight_date_added');
            $table->decimal('asset_value', 12, 2);
            $table->date('date_sold')->nullable();
            $table->decimal('weight_sold_kg', 10, 2)->nullable();
            $table->decimal('price_sold', 12, 2)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pigs');
    }
};
