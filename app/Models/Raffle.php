<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Raffle extends Model
{
    use HasFactory;

    protected $fillable = [
        'owner_id',
        'title',
        'slug',
        'description',
        'cover_image',
        'total_tickets',
        'ticket_price',
        'currency',
        'exchange_rate',
        'draw_type',
        'draw_date',
        'status',
        'sold_count',
        'goal_amount',
        'is_featured',
        'settings',
    ];

    protected function casts(): array
    {
        return [
            'draw_date'  => 'datetime',
            'is_featured' => 'boolean',
            'settings'   => 'array',
            'ticket_price' => 'decimal:2',
            'exchange_rate' => 'decimal:4',
            'goal_amount' => 'decimal:2',
        ];
    }

    protected $appends = [
        'progress_percentage',
        'available_count',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    // ─── Scopes ───────────────────────────────────────────
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    // ─── Helpers ──────────────────────────────────────────
    public function getProgressPercentageAttribute(): float
    {
        if ($this->total_tickets === 0) return 0;
        return round(($this->sold_count / $this->total_tickets) * 100, 2);
    }

    public function getAvailableCountAttribute(): int
    {
        return $this->total_tickets - $this->sold_count;
    }

    // ─── Relaciones ───────────────────────────────────────
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    public function combos(): HasMany
    {
        return $this->hasMany(Combo::class);
    }

    public function drawAudit(): HasOne
    {
        return $this->hasOne(DrawAudit::class);
    }

    public function winners(): HasMany
    {
        return $this->hasMany(Winner::class);
    }

    public function coupons(): HasMany
    {
        return $this->hasMany(Coupon::class);
    }

    public function waitlists(): HasMany
    {
        return $this->hasMany(Waitlist::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
