<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('protocol_rules', function (Blueprint $table) {
            $table->id();

            $table->foreignId('protocol_template_id')
                ->constrained('protocol_templates')
                ->cascadeOnDelete();

            $table->unsignedInteger('sequence_order');

            $table->unsignedInteger('day_offset_start');
            $table->unsignedInteger('day_offset_end')->nullable();

            $table->string('action_name');
            $table->string('action_type');
            $table->string('requirement_level');

            $table->string('condition_key')->nullable();
            $table->text('condition_note')->nullable();

            $table->text('product_note')->nullable();
            $table->text('dosage_note')->nullable();
            $table->text('administration_note')->nullable();
            $table->text('market_note')->nullable();

            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->index(['protocol_template_id', 'sequence_order'], 'protocol_rules_template_sequence_idx');
            $table->index(['protocol_template_id', 'day_offset_start', 'day_offset_end'], 'protocol_rules_template_day_window_idx');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('protocol_rules');
    }
};
