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

            $this->cut($transaction);

            if ($transactionStatusCode == 'confirmed') {
                $this->transfer($transaction);
            }
        });

        return $transactionId;
    }

    public function confirm($request, $transaction)
    {
        DB::transaction(function () use ($request, $transaction) {
            $transaction->update([
                'transaction_status_id' => TransactionStatus::idByCode($request->confirm ? 'confirmed' : 'reject'),
            ]);

            if ($request->confirm) {
                $this->transfer($transaction);
            } else {
                $this->refund($transaction);
            }
        });
    }

    private function cut($transaction)
    {
        $transaction->walletFrom->update([
            'amount' => $transaction->walletFrom->amount - $transaction->amount,
        ]);
    }

    private function transfer($transaction)
    {
        $transaction->walletTo->update([
            'amount' => $transaction->walletTo->amount + ($transaction->amount - $transaction->fee) * $transaction->rate,
        ]);
    }

    private function refund($transaction)
    {
        $transaction->walletFrom->update([
            'amount' => $transaction->walletFrom->amount + $transaction->amount,
        ]);
    }
}