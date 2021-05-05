<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Transactions\StoreTransactionRequest;
use App\Models\LatestCurrencyRate;
use App\Models\Transaction;
use App\Models\TransactionStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransactionController extends Controller
{
    public function store(StoreTransactionRequest $request)
    {
        DB::transaction(function () use ($request) {
            $fee = $request->walletFrom->user_id != $request->walletTo->user_id ? $request->amount * 0.01 : 0;
            $rate = $request->latestRate->rate ?? 1;

            Transaction::create([
                'wallet_from' => $request->wallet_from,
                'wallet_to' => $request->wallet_to,
                'transaction_status_id' => TransactionStatus::idByCode("pending"),
                'amount' => $request->amount,
                'fee' => $fee,
                'rate' => $rate,
            ]);

            $request->walletFrom->update([
                'amount' => $request->walletFrom->amount - $request->amount,
            ]);
            $request->walletTo->update([
                'amount' => $request->walletTo->amount + $request->amount * $rate - $fee,
            ]);
        });

        return [
            'status' => 'OK',
        ];
    }
}
