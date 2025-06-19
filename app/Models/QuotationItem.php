<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuotationItem extends Model
{
    protected $table = 'request_quotation_items';

    protected $fillable = [
        'request_quotation_detail_id',
        'item_name',
        'description',
        'price',
        'quantity',
        'unit'
    ];

    protected $casts = [
        'price' => 'decimal:2'
    ];

    public function quotationDetail(): BelongsTo
    {
        return $this->belongsTo(QuotationDetail::class, 'request_quotation_detail_id');
    }
}