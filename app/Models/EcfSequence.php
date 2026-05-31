<?php

namespace App\Models;

class EcfSequence extends BaseModel
{
    protected $fillable = [
        'company_id',
        'type',
        'description',
        'start_range',
        'end_range',
        'current_sequence',
        'expiration_date',
        'is_active',
    ];

    protected $casts = [
        'expiration_date' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function company(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
