<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'method',
        'amount',
        'currency',
        'reference_number',
        'receipt_image_path',
        'ocr_raw_data',
        'notes',
        'status',
        'reviewed_by',
        'reviewed_at',
        'gateway_response',
    ];

    protected function casts(): array
    {
        return [
            'ocr_raw_data'      => 'array',
            'gateway_response'  => 'array',
            'reviewed_at'       => 'datetime',
            'amount'            => 'decimal:2',
        ];
    }

    // ─── Relaciones ───────────────────────────────────────
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function user(): HasOneThrough
    {
        return $this->hasOneThrough(User::class, Order::class, 'id', 'id', 'order_id', 'user_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
