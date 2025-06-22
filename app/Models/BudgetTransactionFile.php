<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BudgetTransactionFile extends Model
{
    protected $fillable = [
        'budget_transaction_history_id',
        'name',
        'path',
        'size',
        'file_type',
    ];

    public function transactionHistory(): BelongsTo
    {
        return $this->belongsTo(FundTransactionHistory::class, 'budget_transaction_history_id');
    }
}