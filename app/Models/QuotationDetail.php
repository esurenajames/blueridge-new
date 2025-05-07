<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuotationDetail extends Model
{
    protected $table = 'request_quotation_details';

    protected $fillable = [
        'request_quotation_id',
        'company_id',
        'is_selected'
    ];

    protected $casts = [
        'is_selected' => 'boolean'
    ];

    public function quotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class, 'request_quotation_id');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(QuotationItem::class, 'request_quotation_detail_id');
    }
}