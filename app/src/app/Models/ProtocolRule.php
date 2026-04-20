<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProtocolRule extends Model
{
    public const ACTION_MEDICATION = 'medication';
    public const ACTION_VACCINATION = 'vaccination';
    public const ACTION_PROCEDURE = 'procedure';
    public const ACTION_SUPPLEMENT = 'supplement';
    public const ACTION_MANAGEMENT = 'management';

    public const REQUIREMENT_REQUIRED = 'required';
    public const REQUIREMENT_RECOMMENDED = 'recommended';

    public const CONDITION_SEX_MALE = 'sex_male';

    protected $fillable = [
        'protocol_template_id',
        'sequence_order',
        'day_offset_start',
        'day_offset_end',
        'action_name',
        'action_type',
        'requirement_level',
        'condition_key',
        'condition_note',
        'product_note',
        'dosage_note',
        'administration_note',
        'market_note',
        'is_active',
    ];

    protected $casts = [
        'sequence_order' => 'integer',
        'day_offset_start' => 'integer',
        'day_offset_end' => 'integer',
        'is_active' => 'boolean',
    ];

    public function template()
    {
        return $this->belongsTo(ProtocolTemplate::class, 'protocol_template_id');
    }

    public function executions()
    {
        return $this->hasMany(ProtocolExecution::class)
            ->orderBy('scheduled_for_date')
            ->orderBy('id');
    }

    public static function actionTypeOptions(): array
    {
        return [
            self::ACTION_MEDICATION => 'Medication',
            self::ACTION_VACCINATION => 'Vaccination',
            self::ACTION_PROCEDURE => 'Procedure',
            self::ACTION_SUPPLEMENT => 'Supplement',
            self::ACTION_MANAGEMENT => 'Management',
        ];
    }

    public static function requirementLevelOptions(): array
    {
        return [
            self::REQUIREMENT_REQUIRED => 'Required',
            self::REQUIREMENT_RECOMMENDED => 'Recommended',
        ];
    }

    public static function conditionKeyOptions(): array
    {
        return [
            self::CONDITION_SEX_MALE => 'Male Only',
        ];
    }

    public function getActionTypeLabelAttribute(): string
    {
        return static::actionTypeOptions()[$this->action_type]
            ?? ucfirst(str_replace('_', ' ', (string) $this->action_type));
    }

    public function getRequirementLevelLabelAttribute(): string
    {
        return static::requirementLevelOptions()[$this->requirement_level]
            ?? ucfirst(str_replace('_', ' ', (string) $this->requirement_level));
    }

    public function getConditionKeyLabelAttribute(): ?string
    {
        if (!$this->condition_key) {
            return null;
        }

        return static::conditionKeyOptions()[$this->condition_key]
            ?? ucfirst(str_replace('_', ' ', (string) $this->condition_key));
    }

    public function getDueWindowLabelAttribute(): string
    {
        $start = (int) $this->day_offset_start;
        $end = $this->day_offset_end !== null ? (int) $this->day_offset_end : null;

        if ($end === null || $end === $start) {
            return 'Day ' . $start;
        }

        return 'Day ' . $start . '–' . $end;
    }
}
