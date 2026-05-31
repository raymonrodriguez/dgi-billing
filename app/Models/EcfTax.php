<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EcfTax extends Model
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
