<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DrawAudit extends Model
{
    use HasFactory;

    protected $fillable = [
        'raffle_id',
        'participants_hash',
        'participants_snapshot',
        'algorithm_version',
        'seed',
        'execution_mode',
        'external_reference',
        'winning_number',
        'drawn_at',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'participants_snapshot' => 'array',
            'drawn_at'              => 'datetime',
            'winning_number'        => 'integer',
        ];
    }

    public function raffle(): BelongsTo
    {
        return $this->belongsTo(Raffle::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
