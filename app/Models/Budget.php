<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
        'profit',
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
        'profit' => 'decimal:2',
        'balance' => 'decimal:2'
    ];

    public function subcategory()
    {
        return $this->belongsTo(Subcategory::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($budget) {
            if ($budget->proposed_budget > 0) {
                $budget->balance = $budget->proposed_budget;
            }
        });
    }
}