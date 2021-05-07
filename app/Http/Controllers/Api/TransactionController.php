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
        $transactionId = null;

        DB::transaction(function () use ($request, &$transactionId) {
            $fee = $request->walletFrom->user_id != $request->walletTo->user_id ? $request->amount * 0.01 : 0;
            $rate = $request->latestRate->rate ?? 1;
            $transactionStatusCode = $request->walletFrom->user_id == $request->walletTo->user_id ? 'confirmed' : 'pending';

            $transaction = Transaction::create([
                'wallet_from' => $request->wallet_from,
                'wallet_to' => $request->wallet_to,
                'transaction_status_id' => TransactionStatus::idByCode($transactionStatusCode),
                'amount' => $request->amount,
                'fee' => $fee,
                'rate' => $rate,
            ]);
            $transactionId = $transaction->id;

            $request->walletFrom->update([
                'amount' => $request->walletFrom->amount - $request->amount,
            ]);

            if ($transactionStatusCode == 'confirmed') {
                $request->walletTo->update([
                    'amount' => $request->walletTo->amount + $request->amount * $rate - $fee,
                ]);
            }
        });

        return [
            'status' => 'OK',
            'transactionId' => $transactionId,
        ];
    }

    public function confirm(ConfirmTransactionRequest $request, Transaction $transaction)
    {
        DB::transaction(function () use ($request, $transaction) {
            $transactionStatusCode = $request->confirm ? 'confirmed' : 'reject';

            $transaction->update([
                'transaction_status_id' => TransactionStatus::idByCode($transactionStatusCode),
            ]);

            if ($request->confirm) {
                $transaction->walletTo->update([
                    'amount' => $transaction->walletTo->amount + ($transaction->amount - $transaction->fee) * $transaction->rate,
                ]);
            } else {
                $transaction->walletFrom->update([
                    'amount' => $transaction->walletFrom->amount + $transaction->amount,
                ]);
            }
        });

        return [
            'status' => 'OK',
        ];
    }
}
