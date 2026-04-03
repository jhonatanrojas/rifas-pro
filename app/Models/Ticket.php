<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Ticket extends Model
{
    use HasFactory;

    protected $fillable = [
        'raffle_id',
        'number',
        'status',
        'reserved_at',
        'reserved_until',
        'user_id',
        'order_id',
        'version',
    ];

    protected function casts(): array
    {
        return [
            'reserved_at'    => 'datetime',
            'reserved_until' => 'datetime',
        ];
    }

    // ─── Scopes ───────────────────────────────────────────
    public function scopeAvailable($query)
    {
        return $query->where('status', 'available');
    }

    public function scopeReserved($query)
    {
        return $query->where('status', 'reserved');
    }

    public function scopeSold($query)
    {
        return $query->where('status', 'sold');
    }

    public function scopeExpiredReservations($query)
    {
        return $query->where('status', 'reserved')
                     ->where('reserved_until', '<', now());
    }

    // ─── Relaciones ───────────────────────────────────────
    public function raffle(): BelongsTo
    {
        return $this->belongsTo(Raffle::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function orders(): BelongsToMany
    {
        return $this->belongsToMany(Order::class, 'order_tickets');
    }
}
