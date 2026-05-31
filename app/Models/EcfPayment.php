<?php

namespace App\Models;

class EcfPayment extends BaseModel
{
    protected $fillable = [
        'ecf_id',
        'method',
        'amount',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function ecf(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Ecf::class);
    }
}
