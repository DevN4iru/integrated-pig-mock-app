<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pigs', function (Blueprint $table) {
            $table->foreignId('sire_boar_id')
                ->nullable()
                ->after('mother_sow_id')
                ->constrained('pigs')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('pigs', function (Blueprint $table) {
            $table->dropForeign(['sire_boar_id']);
            $table->dropColumn('sire_boar_id');
        });
    }
};
