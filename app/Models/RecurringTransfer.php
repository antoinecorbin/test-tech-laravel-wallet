<?php

namespace App\Models;

use App\Enums\RecurringTransferStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RecurringTransfer extends Model
{
    use HasFactory;

    protected $fillable = [
        'source_id',
        'recipient_email',
        'reason',
        'amount',
        'frequency_days',
        'start_date',
        'end_date',
        'status',
        'last_execution'
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'last_execution' => 'datetime',
        'status' => RecurringTransferStatus::class
    ];

    public function source(): BelongsTo
    {
        return $this->belongsTo(Wallet::class, 'source_id');
    }
}
