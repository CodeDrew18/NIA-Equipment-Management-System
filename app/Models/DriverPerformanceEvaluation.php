<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DriverPerformanceEvaluation extends Model
{
    use HasFactory;

    protected $table = 'driver_performance_evaluations';

    protected $fillable = [
        'transportation_request_form_id',
        'copy_key',
        'copy_number',
        'driver_name',
        'status',
        'overall_rating',
        'timeliness_score',
        'safety_score',
        'compliance_score',
        'evaluator_name',
        'evaluation_payload',
        'attachment',
        'comments',
        'evaluated_at',
    ];

    protected $casts = [
        'copy_number' => 'integer',
        'overall_rating' => 'decimal:2',
        'timeliness_score' => 'integer',
        'safety_score' => 'integer',
        'compliance_score' => 'integer',
        'evaluation_payload' => 'array',
        'attachment' => 'array',
        'evaluated_at' => 'datetime',
    ];

    public function transportationRequestForm(): BelongsTo
    {
        return $this->belongsTo(TransportationRequestFormModel::class, 'transportation_request_form_id');
    }
}
