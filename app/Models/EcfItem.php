<?php

namespace App\Models;

class EcfItem extends BaseModel
{
    protected $touches = ['ecf'];

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

    public function getCalculatedAdditionalTaxes(): array
    {
        if (empty($this->additional_taxes)) {
            return [];
        }

        $taxes = [];
        foreach ($this->additional_taxes as $code) {
            $rate = $this->getAdditionalTaxRate($code);
            $taxes[] = [
                'code' => $code,
                'rate' => $rate,
                'amount' => round($this->subtotal * ($rate / 100), 2),
            ];
        }

        return $taxes;
    }

    protected function getAdditionalTaxRate(string $code): float
    {
        return match ($code) {
            '001' => 10.00, // Propina Legal
            '002' => 2.00,  // CDT
            '003' => 16.00, // Seguros
            default => 0.00,
        };
    }
}
