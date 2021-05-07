<?php

namespace App\Services\Transactions;

use Illuminate\Support\Facades\Facade;

class TransactionsFacade extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'App\Services\Transactions\Transactions';
    }
}