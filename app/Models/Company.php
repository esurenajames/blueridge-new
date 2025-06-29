<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'company_name',
        'contact_person',
        'address',
        'contact_number',
        'email'
    ];

    public function quotationDetails(): HasMany
    {
        return $this->hasMany(QuotationDetail::class);
    }
}