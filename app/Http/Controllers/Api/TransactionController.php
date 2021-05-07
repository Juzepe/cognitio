<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Transactions\ConfirmTransactionRequest;
use App\Http\Requests\Transactions\StoreTransactionRequest;
use App\Models\Transaction;
use App\Models\TransactionStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransactionController extends Controller
{
    public function store(StoreTransactionRequest $request)
    {
        return [
            'status' => 'OK',
            'transactionId' => \Transactions::create($request),
        ];
    }

    public function confirm(ConfirmTransactionRequest $request, Transaction $transaction)
    {
        \transactions::confirm($request, $transaction);

        return [
            'status' => 'OK',
        ];
    }
}
