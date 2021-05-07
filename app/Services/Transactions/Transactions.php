<?php

namespace App\Services\Transactions;

use App\Models\Transaction;
use App\Models\TransactionStatus;
use Illuminate\Support\Facades\DB;

class Transactions
{
    public function create($request)
    {
        $transaction = null;

        DB::transaction(function () use ($request, &$transaction) {
            $transactionStatusCode = $this->walletsHasSameUser($request) ? 'confirmed' : 'pending';

            $transaction = Transaction::create($request->validated() + [
                    'transaction_status_id' => TransactionStatus::idByCode($transactionStatusCode),
                    'fee' => $this->walletsHasSameUser($request) ? 0 : $request->amount * 0.01,
                    'rate' => $request->latestRate->rate ?? 1,
                ]);

            $this->cut($transaction);

            if ($transactionStatusCode == 'confirmed') {
                $this->transfer($transaction);
            }
        });

        return $transaction->id ?? null;
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

    private function walletsHasSameUser($request): bool
    {
        return $request->walletFrom->user_id == $request->walletTo->user_id;
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