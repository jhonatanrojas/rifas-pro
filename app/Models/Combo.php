<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Combo extends Model
{
    use HasFactory;

    protected $fillable = ['raffle_id', 'quantity', 'price', 'label', 'is_active'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'price'     => 'decimal:2',
        ];
    }

    public function raffle(): BelongsTo
    {
        return $this->belongsTo(Raffle::class);
    }

    /**
     * Calcula el ahorro comparado con el precio individual de tickets.
     */
    public function getSavingsAttribute(): float
    {
        $individualTotal = $this->raffle?->ticket_price * $this->quantity ?? 0;
        return max(0, $individualTotal - $this->price);
    }
}
