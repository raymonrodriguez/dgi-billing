<?php

namespace App\Models;

use App\Enums\CommercialApprovalStatus;
use App\Enums\EcfStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ecf extends Model
{
    protected $fillable = [
        'company_id',
        'user_id',
        'contact_id',
        'encf',
        'type',
        'total_amount',
        'total_tax',
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
        'dgii_status' => EcfStatus::class,
        'commercial_approval_status' => CommercialApprovalStatus::class,
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
}
