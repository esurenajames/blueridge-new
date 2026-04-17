<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Budget extends Model
{
    use HasFactory;

    protected $table = 'budget';

    protected $fillable = [
        'subcategory_id',
        'proposed_budget',
        'january',
        'february',
        'march',
        'april',
        'may',
        'june',
        'july',
        'august',
        'september',
        'october',
        'november',
        'december',
        'income',
        'balance'
    ];

    protected $casts = [
        'proposed_budget' => 'decimal:2',
        'january' => 'decimal:2',
        'february' => 'decimal:2',
        'march' => 'decimal:2',
        'april' => 'decimal:2',
        'may' => 'decimal:2',
        'june' => 'decimal:2',
        'july' => 'decimal:2',
        'august' => 'decimal:2',
        'september' => 'decimal:2',
        'october' => 'decimal:2',
        'november' => 'decimal:2',
        'december' => 'decimal:2',
        'income' => 'decimal:2',
        'balance' => 'decimal:2'
    ];

    protected $appends = ['jan_jun', 'jul_dec'];

    public function getJanJunAttribute(): float
    {
        return (float) (($this->january ?? 0) + ($this->february ?? 0) + ($this->march ?? 0) + 
               ($this->april ?? 0) + ($this->may ?? 0) + ($this->june ?? 0));
    }

    public function getJulDecAttribute(): float
    {
        return (float) (($this->july ?? 0) + ($this->august ?? 0) + ($this->september ?? 0) + 
               ($this->october ?? 0) + ($this->november ?? 0) + ($this->december ?? 0));
    }

    public function subcategory()
    {
        return $this->belongsTo(Subcategory::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(FundTransactionHistory::class);
    }
}