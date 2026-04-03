<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Affiliate extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'code',
        'commission_rate',
        'total_earned',
        'total_withdrawn',
    ];

    protected function casts(): array
    {
        return [
            'commission_rate'   => 'decimal:4',
            'total_earned'      => 'decimal:4',
            'total_withdrawn'   => 'decimal:4',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function referralConversions(): HasMany
    {
        return $this->hasMany(ReferralConversion::class);
    }

    public function getPendingBalanceAttribute(): float
    {
        return $this->total_earned - $this->total_withdrawn;
    }
}
