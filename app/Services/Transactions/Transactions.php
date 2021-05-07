<?php

namespace App\Services\Transactions;

use App\Models\Transaction;
use App\Models\TransactionStatus;
use Illuminate\Support\Facades\DB;

class Transactions
{
    public function create($request)
    {
        $transactionId = null;

        DB::transaction(function () use ($request, &$transactionId) {
            $fee = $request->walletFrom->user_id != $request->walletTo->user_id ? $request->amount * 0.01 : 0;
            $rate = $request->latestRate->rate ?? 1;
            $transactionStatusCode = $request->walletFrom->user_id == $request->walletTo->user_id ? 'confirmed' : 'pending';

            $transaction = Transaction::create($request->validated() + [
                    'transaction_status_id' => TransactionStatus::idByCode($transactionStatusCode),
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

        return $transactionId;
    }

    public function confirm($request, $transaction)
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
    }
}