<?php

namespace Tests\Feature\Transactions;

use App\Models\Currency;
use App\Models\LatestCurrencyRate;
use App\Models\Transaction;
use App\Models\Wallet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TransactionCalculationTest extends TestCase
{
    use RefreshDatabase;

    private $wallet;
    private $anotherWallet;
    private $anotherUserWallet;

    public function setUp(): void
    {
        parent::setUp();

        $this->seed();

        $this->wallet = Wallet::factory()->create([
            'currency_id' => Currency::idByCode("GEL"),
            'amount' => 1000,
        ]);
        $this->anotherWallet = Wallet::factory()->create([
            'user_id' => $this->wallet->user_id,
            'currency_id' => Currency::idByCode("USD"),
            'amount' => 1000,
        ]);
        $this->anotherUserWallet = Wallet::factory()->create([
            'currency_id' => Currency::idByCode("USD"),
            'amount' => 1000,
        ]);

        LatestCurrencyRate::factory()->create([
            'currency_from' => $this->wallet->currency_id,
            'currency_to' => $this->anotherWallet->currency_id,
            'rate' => 0.3,
        ]);
    }

    public function test_transaction_between_same_user_wallets()
    {
        Sanctum::actingAs($this->wallet->user);

        $response = $this->post('/api/transactions', [
            'wallet_from' => $this->wallet->id,
            'wallet_to' => $this->anotherWallet->id,
            'amount' => 10,
        ], ['Accept' => 'application/json'])->json();

        $transaction = Transaction::find($response['transactionId']);

        $this->assertTrue($transaction->transactionStatus->code == 'confirmed');
        $this->assertTrue($transaction->amount == 10);
        $this->assertTrue($transaction->fee == 0);
        $this->assertTrue($transaction->rate == 0.3);

        $this->wallet->refresh();
        $this->anotherWallet->refresh();

        $this->assertTrue($this->wallet->amount == 990);
        $this->assertTrue($this->anotherWallet->amount == 1003);
    }

    public function test_rejected_transaction_between_different_users_wallets()
    {
        Sanctum::actingAs($this->wallet->user);

        $response = $this->post('/api/transactions', [
            'wallet_from' => $this->wallet->id,
            'wallet_to' => $this->anotherUserWallet->id,
            'amount' => 10,
        ], ['Accept' => 'application/json'])->json();

        $transaction = Transaction::find($response['transactionId']);

        $this->assertTrue($transaction->transactionStatus->code == 'pending');
        $this->assertTrue($transaction->amount == 10);
        $this->assertTrue($transaction->fee == 0.1);
        $this->assertTrue($transaction->rate == 0.3);

        $this->wallet->refresh();
        $this->anotherUserWallet->refresh();

        $this->assertTrue($this->wallet->amount == 990);
        $this->assertTrue($this->anotherUserWallet->amount == 1000);

        Sanctum::actingAs($this->anotherUserWallet->user);

        $this->post('/api/transactions/' . $transaction->id . '/confirm', [
            'confirm' => false,
        ], ['Accept' => 'application/json']);

        $transaction->refresh();
        $this->wallet->refresh();
        $this->anotherUserWallet->refresh();

        $this->assertTrue($transaction->transactionStatus->code == 'reject');
        $this->assertTrue($this->wallet->amount == 1000);
        $this->assertTrue($this->anotherUserWallet->amount == 1000);
    }

    public function test_confirmed_transaction_between_different_users_wallets()
    {
        Sanctum::actingAs($this->wallet->user);

        $response = $this->post('/api/transactions', [
            'wallet_from' => $this->wallet->id,
            'wallet_to' => $this->anotherUserWallet->id,
            'amount' => 10,
        ], ['Accept' => 'application/json'])->json();

        $transaction = Transaction::find($response['transactionId']);

        Sanctum::actingAs($this->anotherUserWallet->user);

        $this->post('/api/transactions/' . $transaction->id . '/confirm', [
            'confirm' => true,
        ], ['Accept' => 'application/json']);

        $transaction->refresh();
        $this->wallet->refresh();
        $this->anotherUserWallet->refresh();

        $this->assertTrue($transaction->transactionStatus->code == 'confirmed');
        $this->assertTrue($this->wallet->amount == 990);
        $this->assertTrue($this->anotherUserWallet->amount == 1002.97);
    }
}
