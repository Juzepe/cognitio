<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transaction extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'wallet_from',
        'wallet_to',
        'transaction_status_id',
        'amount',
        'fee',
        'rate',
    ];

    public function walletFrom()
    {
        return $this->belongsTo(Wallet::class, 'wallet_from');
    }

    public function walletTo()
    {
        return $this->belongsTo(Wallet::class, 'wallet_to');
    }

    public function transactionStatus()
    {
        return $this->belongsTo(TransactionStatus::class);
    }
}
