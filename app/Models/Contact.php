<?php

namespace App\Models;

class Contact extends BaseModel
{
    protected $fillable = [
        'company_id',
        'name',
        'document_type',
        'tax_id',
        'email',
        'phone',
        'address',
        'is_electronic_receiver',
        'is_active',
    ];

    protected $casts = [
        'is_electronic_receiver' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function company(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
