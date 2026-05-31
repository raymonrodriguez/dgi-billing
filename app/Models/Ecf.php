<?php

namespace App\Models;

use App\Enums\CommercialApprovalStatus;
use App\Enums\EcfStatus;
use App\Enums\Currency;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ecf extends BaseModel
{
    protected $fillable = [
        'company_id',
        'user_id',
        'contact_id',
        'encf',
        'modified_ncf',
        'exemption_id',
        'income_type',
        'type',
        'currency',
        'exchange_rate',
        'total_amount',
        'total_tax',
        'total_additional_taxes',
        'dgii_status',
        'commercial_approval_status',
        'track_id',
        'security_code',
        'signed_xml_path',
        'pdf_path',
        'dgii_response',
        'dgii_messages',
        'issued_at',
    ];

    protected $casts = [
        'dgii_response' => 'array',
        'dgii_messages' => 'array',
        'issued_at' => 'datetime',
        'total_amount' => 'decimal:2',
        'total_tax' => 'decimal:2',
        'total_additional_taxes' => 'decimal:2',
        'exchange_rate' => 'decimal:4',
        'dgii_status' => EcfStatus::class,
        'commercial_approval_status' => CommercialApprovalStatus::class,
        'currency' => Currency::class,
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(EcfItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(EcfPayment::class);
    }

    public function taxes(): HasMany
    {
        return $this->hasMany(EcfTax::class);
    }

    /**
     * Get all logs for this ECF and its items.
     */
    public function getAllActivityLogsAttribute(): \Illuminate\Support\Collection
    {
        $ecfLogs = $this->activityLogs()->get();

        $itemLogs = Log::where('model', 'EcfItem')
            ->whereIn('record_id', $this->items()->pluck('id'))
            ->get();

        return $ecfLogs->concat($itemLogs)->sortByDesc('created_at');
    }

    /**
     * Get amount converted to DOP based on exchange rate.
     */
    public function toDop(float $amount): float
    {
        return round($amount * (float) $this->exchange_rate, 2);
    }

    /**
     * Check if the invoice is in a foreign currency.
     */
    public function isForeignCurrency(): bool
    {
        return $this->currency !== \App\Enums\Currency::DOP;
    }
}
