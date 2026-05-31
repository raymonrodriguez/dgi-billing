<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contingency extends Model
{
    protected $fillable = [
        'company_id',
        'reason',
        'start_date',
        'end_date',
        'dgii_track_id',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
    ];

    public function company(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
