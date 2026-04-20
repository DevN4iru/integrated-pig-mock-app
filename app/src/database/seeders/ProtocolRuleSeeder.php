<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProtocolRuleSeeder extends Seeder
{
    public function run(): void
    {
        $pigletTemplateId = DB::table('protocol_templates')
            ->where('code', 'piglet_core_program')
            ->value('id');

        $sowTemplateId = DB::table('protocol_templates')
            ->where('code', 'lactating_sow_core_program')
            ->value('id');

        if (!$pigletTemplateId || !$sowTemplateId) {
            return;
        }

        // Clear existing rules for idempotency
        DB::table('protocol_rules')->whereIn('protocol_template_id', [
            $pigletTemplateId,
            $sowTemplateId
        ])->delete();

        // =========================
        // PIGLET RULES
        // =========================
        $pigletRules = [
            [10, 1, 3, 'Apralyte support', 'supplement', 'recommended', null, null, 'Apralyte'],
            [20, 3, 3, 'Iron administration', 'medication', 'required', null, null, 'Jectran Premium'],
            [30, 7, 7, 'Mycoplasma vaccination', 'vaccination', 'recommended', null, null, 'Respisure'],
            [40, 10, 10, 'B-complex administration', 'supplement', 'recommended', null, null, 'Bexan SP'],
            [50, 11, 11, 'Castration', 'procedure', 'recommended', 'sex_male', 'male piglets only', null],
            [60, 10, 14, 'Vetracin support window', 'medication', 'recommended', null, null, 'Vetracin Premium / Gold'],
            [70, 14, 14, 'Iron booster', 'medication', 'recommended', null, null, 'Jectran Premium'],
            [80, 21, 21, 'Hog cholera vaccination', 'vaccination', 'recommended', null, null, 'Coglapest'],
            [90, 25, 25, 'B-complex repeat', 'supplement', 'recommended', null, null, 'Bexan SP'],
            [100, 23, 33, 'Anti-stress support', 'supplement', 'recommended', null, null, 'Digestaide / Aquadox / Vetracin Gold'],
            [110, 35, 35, 'Deworming', 'medication', 'recommended', null, null, 'Latigo1000'],
        ];

        foreach ($pigletRules as $rule) {
            DB::table('protocol_rules')->insert([
                'protocol_template_id' => $pigletTemplateId,
                'sequence_order' => $rule[0],
                'day_offset_start' => $rule[1],
                'day_offset_end' => $rule[2],
                'action_name' => $rule[3],
                'action_type' => $rule[4],
                'requirement_level' => $rule[5],
                'condition_key' => $rule[6],
                'condition_note' => $rule[7],
                'product_note' => $rule[8],
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // =========================
        // LACTATING SOW RULES
        // =========================
        $sowRules = [
            [10, 1, 1, 'Long-acting antibiotic', 'medication', 'recommended', null, null, '10–15ml'],
            [20, 2, 2, 'B-complex administration', 'supplement', 'recommended', null, null, '5ml'],
            [30, 6, 6, 'Mycoplasma vaccination', 'vaccination', 'recommended', null, null, 'Respisure'],
            [40, 14, 14, 'Parvo vaccination', 'vaccination', 'recommended', null, null, 'Farrowsure 5ml'],
            [50, 21, 21, 'Hog cholera vaccination', 'vaccination', 'recommended', null, null, null],
            [60, 28, 28, 'Weaning', 'management', 'required', null, null, null],
            [70, 29, 29, 'Deworming', 'medication', 'recommended', null, null, null],
            [80, 30, 30, 'B-complex + Vitamin ADE', 'supplement', 'recommended', null, null, '5ml + 5ml'],
        ];

        foreach ($sowRules as $rule) {
            DB::table('protocol_rules')->insert([
                'protocol_template_id' => $sowTemplateId,
                'sequence_order' => $rule[0],
                'day_offset_start' => $rule[1],
                'day_offset_end' => $rule[2],
                'action_name' => $rule[3],
                'action_type' => $rule[4],
                'requirement_level' => $rule[5],
                'condition_key' => $rule[6],
                'condition_note' => $rule[7],
                'product_note' => $rule[8],
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
