<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Wallet extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'currency_id',
        'name',
        'amount',
        'is_active',
    ];

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }
}
