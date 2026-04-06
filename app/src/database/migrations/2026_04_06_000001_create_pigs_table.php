<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pigs', function (Blueprint $table) {
            $table->id();
            $table->string('ear_tag')->unique();
            $table->string('breed');
            $table->string('sex');
            $table->string('pen_location');
            $table->string('pig_source');
            $table->date('date_added');
            $table->decimal('latest_weight', 8, 2);
            $table->decimal('asset_value', 10, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pigs');
    }
};