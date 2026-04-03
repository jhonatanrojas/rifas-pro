<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Coupon extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'type',
        'value',
        'max_uses',
        'used_count',
        'valid_from',
        'valid_until',
        'raffle_id',
    ];

    protected function casts(): array
    {
        return [
            'value'       => 'decimal:2',
            'valid_from'  => 'datetime',
            'valid_until' => 'datetime',
        ];
    }

    // ─── Scopes ───────────────────────────────────────────
    public function scopeActive($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('valid_from')->orWhere('valid_from', '<=', now());
        })->where(function ($q) {
            $q->whereNull('valid_until')->orWhere('valid_until', '>=', now());
        })->whereRaw('used_count < max_uses');
    }

    // ─── Helpers ──────────────────────────────────────────
    public function isValid(): bool
    {
        if ($this->used_count >= $this->max_uses) return false;
        if ($this->valid_from && $this->valid_from->isFuture()) return false;
        if ($this->valid_until && $this->valid_until->isPast()) return false;
        return true;
    }

    // ─── Relaciones ───────────────────────────────────────
    public function raffle(): BelongsTo
    {
        return $this->belongsTo(Raffle::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
