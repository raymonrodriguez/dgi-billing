<?php

namespace App\Models;

use App\Enums\DgiiEnvironment;
use Filament\Models\Contracts\HasName;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Company extends BaseModel implements HasName
{
    use HasUuids;
    protected $fillable = [
        'company_name',
        'trade_name',
        'logo',
        'tax_id',
        'environment',
        'certificate',
        'cert_password',
        'is_active',
    ];

    public function getFilamentName(): string
    {
        return $this->company_name;
    }

    protected $casts = [
        'is_active' => 'boolean',
        'cert_password' => 'encrypted',
        'environment' => DgiiEnvironment::class,
    ];

    public function users(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    public function contacts(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Contact::class);
    }

    public function ecfSequences(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(EcfSequence::class);
    }

    public function ecfs(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Ecf::class);
    }

    public function dgiiTokens(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(DgiiToken::class);
    }
}
