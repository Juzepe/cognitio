<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LatestCurrencyRate extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'currency_from',
        'currency_to',
        'rate',
    ];
}
