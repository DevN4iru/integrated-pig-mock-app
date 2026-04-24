<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Medication extends Model
{
    protected $fillable = [
        'pig_id',
        'protocol_execution_id',
        'medication_name',
        'dosage',
        'cost',
        'administered_at',
        'notes',
    ];

    protected $casts = [
        'cost' => 'float',
    ];

    public function pig()
    {
        return $this->belongsTo(Pig::class);
    }

    public function protocolExecution()
    {
        return $this->belongsTo(ProtocolExecution::class, 'protocol_execution_id');
    }
}
