<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Winner extends Model
{
    use HasFactory;

    protected $fillable = [
        'raffle_id',
        'ticket_id',
        'user_id',
        'prize_description',
        'notified_at',
        'claimed_at',
        'testimony',
        'photo_path',
    ];

    protected function casts(): array
    {
        return [
            'notified_at' => 'datetime',
            'claimed_at'  => 'datetime',
        ];
    }

    public function raffle(): BelongsTo
    {
        return $this->belongsTo(Raffle::class);
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
