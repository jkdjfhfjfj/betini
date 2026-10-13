<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CurrencyRate extends Model
{
    protected $table = 'currency_rates';

    protected $fillable = [
        'currency_code',
        'usd_to_currency',
        'source',
        'fetched_at',
    ];

    protected $casts = [
        'usd_to_currency' => 'decimal:10',
        'fetched_at' => 'datetime',
    ];
}
