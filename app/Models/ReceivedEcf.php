<?php

namespace App\Models;

use App\Enums\CommercialApprovalStatus;

class ReceivedEcf extends BaseModel
{
    protected $fillable = [
        'company_id',
        'rnc_emisor',
        'encf',
        'total_amount',
        'commercial_approval_status',
        'received_xml_path',
        'arecf_sent',
        'acecf_sent',
    ];

    protected $casts = [
        'arecf_sent' => 'boolean',
        'acecf_sent' => 'boolean',
        'total_amount' => 'decimal:2',
        'commercial_approval_status' => CommercialApprovalStatus::class,
    ];

    public function company(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
