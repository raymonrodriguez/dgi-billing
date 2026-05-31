<?php

namespace App\Models;

class EcfTax extends BaseModel
{
    protected $fillable = [
        'ecf_id',
        'type',
        'rate',
        'amount',
    ];

    protected $casts = [
        'rate' => 'decimal:2',
        'amount' => 'decimal:2',
    ];

    public function ecf(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Ecf::class);
    }
}
