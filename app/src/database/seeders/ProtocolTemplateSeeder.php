<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProtocolTemplateSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('protocol_templates')->updateOrInsert(
            ['code' => 'piglet_core_program'],
            [
                'name' => 'Piglet Core Program',
                'description' => 'Standard piglet medication and management schedule.',
                'target_type' => 'piglet',
                'anchor_event' => 'birth',
                'is_active' => true,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        DB::table('protocol_templates')->updateOrInsert(
            ['code' => 'lactating_sow_core_program'],
            [
                'name' => 'Lactating Sow Core Program',
                'description' => 'Standard lactating sow medication and management schedule.',
                'target_type' => 'lactating_sow',
                'anchor_event' => 'farrowing',
                'is_active' => true,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );
    }
}
