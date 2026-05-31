<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EcfItem extends Model
{
    protected $fillable = [
        'ecf_id',
        'description',
        'quantity',
        'price',
        'discount',
        'subtotal',
        'billing_indicator',
        'additional_taxes',
    ];

    protected $casts = [
        'quantity' => 'decimal:4',
        'price' => 'decimal:2',
        'discount' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'additional_taxes' => 'array',
        'billing_indicator' => \App\Enums\ItbisIndicator::class,
    ];

    public function ecf(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Ecf::class);
    }
}
