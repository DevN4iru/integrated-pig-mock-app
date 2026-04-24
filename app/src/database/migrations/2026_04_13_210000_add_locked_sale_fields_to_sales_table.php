<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->decimal('weight_at_sale', 10, 2)->nullable()->after('price');
            $table->decimal('price_per_kg_at_sale', 10, 2)->nullable()->after('weight_at_sale');
            $table->decimal('recommended_price', 10, 2)->nullable()->after('price_per_kg_at_sale');
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn([
                'weight_at_sale',
                'price_per_kg_at_sale',
                'recommended_price',
            ]);
        });
    }
};
