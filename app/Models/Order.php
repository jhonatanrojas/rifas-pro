<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'raffle_id',
        'status',
        'subtotal',
        'discount',
        'total',
        'currency',
        'exchange_rate_snapshot',
        'coupon_id',
        'payment_method',
        'paid_at',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'paid_at'   => 'datetime',
            'metadata'  => 'array',
            'subtotal'  => 'decimal:2',
            'discount'  => 'decimal:2',
            'total'     => 'decimal:2',
            'exchange_rate_snapshot' => 'decimal:4',
        ];
    }

    // ─── Relaciones ───────────────────────────────────────
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function raffle(): BelongsTo
    {
        return $this->belongsTo(Raffle::class);
    }

    public function tickets(): BelongsToMany
    {
        return $this->belongsToMany(Ticket::class, 'order_tickets');
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }
}
