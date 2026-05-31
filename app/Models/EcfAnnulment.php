<?php

namespace App\Models;

use App\Enums\AnnulmentStatus;

class EcfAnnulment extends BaseModel
{
    protected $fillable = [
        'company_id',
        'type',
        'start_sequence',
        'end_sequence',
        'quantity',
        'reason',
        'status',
        'xml_path',
        'response',
    ];

    protected $casts = [
        'response' => 'array',
        'status' => AnnulmentStatus::class,
    ];

    public function company(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
