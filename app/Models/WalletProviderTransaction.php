<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WalletProviderTransaction extends Model
{
    protected $table = 'wallet_provider_transactions';

    protected $guarded = ['id'];

    protected $casts = [
        'metadata' => 'array',
        'usd_rate' => 'decimal:10',
        'amount' => 'decimal:4',
        'fee' => 'decimal:4',
        'net_amount' => 'decimal:4',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
