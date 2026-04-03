<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Raffle extends Model
{
    use HasFactory;

    protected $fillable = [
        'owner_id',
        'title',
        'slug',
        'description',
        'category',
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
            'total_tickets' => 'integer',
            'sold_count' => 'integer',
            'ticket_price' => 'decimal:2',
            'exchange_rate' => 'decimal:4',
            'goal_amount' => 'decimal:2',
        ];
    }

    protected $appends = [
        'progress_percentage',
        'available_count',
        'number_digits',
        'number_range_label',
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
        $totalTickets = (int) $this->total_tickets;
        if ($totalTickets <= 0) {
            return 0;
        }

        return round(((int) $this->sold_count / $totalTickets) * 100, 2);
    }

    public function getAvailableCountAttribute(): int
    {
        return max(0, (int) $this->total_tickets - (int) $this->sold_count);
    }

    public function getNumberDigitsAttribute(): int
    {
        $total = max(1, (int) $this->total_tickets);
        $maxNumber = max(0, $total - 1);

        return max(3, strlen((string) $maxNumber));
    }

    public function getNumberRangeLabelAttribute(): string
    {
        $digits = $this->number_digits;
        $maxNumber = max(0, (int) $this->total_tickets - 1);

        return str_pad('0', $digits, '0', STR_PAD_LEFT) . ' - ' . str_pad((string) $maxNumber, $digits, '0', STR_PAD_LEFT);
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

    public function prizes(): HasMany
    {
        return $this->hasMany(RafflePrize::class)->orderBy('sort_order');
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

    public function payments(): HasManyThrough
    {
        return $this->hasManyThrough(Payment::class, Order::class);
    }
}
