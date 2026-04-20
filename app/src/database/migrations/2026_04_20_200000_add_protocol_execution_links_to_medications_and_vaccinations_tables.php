<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('medications', function (Blueprint $table) {
            $table->foreignId('protocol_execution_id')
                ->nullable()
                ->after('pig_id')
                ->constrained('protocol_executions')
                ->cascadeOnDelete();

            $table->unique(
                'protocol_execution_id',
                'medications_protocol_execution_unique'
            );
        });

        Schema::table('vaccinations', function (Blueprint $table) {
            $table->foreignId('protocol_execution_id')
                ->nullable()
                ->after('pig_id')
                ->constrained('protocol_executions')
                ->cascadeOnDelete();

            $table->unique(
                'protocol_execution_id',
                'vaccinations_protocol_execution_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::table('vaccinations', function (Blueprint $table) {
            $table->dropUnique('vaccinations_protocol_execution_unique');
            $table->dropConstrainedForeignId('protocol_execution_id');
        });

        Schema::table('medications', function (Blueprint $table) {
            $table->dropUnique('medications_protocol_execution_unique');
            $table->dropConstrainedForeignId('protocol_execution_id');
        });
    }
};
