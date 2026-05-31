<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EcfPayment extends Model
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
