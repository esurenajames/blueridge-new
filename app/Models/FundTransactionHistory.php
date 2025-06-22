<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FundTransactionHistory extends Model
{
    protected $table = 'budget_transaction_history';

    protected $fillable = [
        'request_id',
        'budget_id',
        'processed_by',
        'transaction_date',
        'type',
        'total_amount',
        'remarks'
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'total_amount' => 'decimal:2',
        'type' => 'string'
    ];

    public function request(): BelongsTo
    {
        return $this->belongsTo(Request::class);
    }

    public function budget(): BelongsTo
    {
        return $this->belongsTo(Budget::class);
    }

    public function files(): HasMany
    {
        return $this->hasMany(BudgetTransactionFile::class, 'budget_transaction_history_id');
    }

    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeBetweenDates($query, string $start, string $end)
    {
        return $query->whereBetween('transaction_date', [$start, $end]);
    }
}