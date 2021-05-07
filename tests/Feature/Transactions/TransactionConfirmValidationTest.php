<?php

namespace Tests\Feature\Transactions;

use App\Models\Currency;
use App\Models\Transaction;
use App\Models\Wallet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TransactionConfirmValidationTest extends TestCase
{
    use RefreshDatabase;

    private $wallet;
    private $anotherWallet;
    private $transaction;
    private $anotherTransaction;

    public function setUp(): void
    {
        parent::setUp();

        $this->seed();

        $this->wallet = Wallet::factory()->create([
            'currency_id' => Currency::idByCode("GEL"),
        ]);
        $this->anotherWallet = Wallet::factory()->create([
            'currency_id' => Currency::idByCode("USD"),
        ]);
        $this->transaction = Transaction::factory()->create([
            'wallet_from' => $this->wallet->id,
            'wallet_to' => $this->anotherWallet->id,
        ]);
        $this->anotherTransaction = Transaction::factory()->create([
            'wallet_from' => $this->anotherWallet->id,
            'wallet_to' => $this->wallet->id,
        ]);
    }

    public function test_transaction_confirm_unauthorized()
    {
        $this->post('/api/transactions/1000/confirm', [], ['Accept' => 'application/json'])
            ->assertStatus(401);

        Sanctum::actingAs($this->wallet->user);

        $this->post('/api/transactions/' . $this->transaction->id . '/confirm', [], ['Accept' => 'application/json'])
            ->assertStatus(401);
    }

    public function test_transaction_confirm_without_data()
    {
        Sanctum::actingAs($this->wallet->user);

        $this->post('/api/transactions/' . $this->anotherTransaction->id . '/confirm', [], ['Accept' => 'application/json'])
            ->assertStatus(400)
            ->assertSeeText('The confirm field is required.');
    }

    public function test_transaction_confirm_with_incorrect_confirm()
    {
        Sanctum::actingAs($this->wallet->user);

        $this->post('/api/transactions/' . $this->anotherTransaction->id . '/confirm', [
            'confirm' => 7,
        ], ['Accept' => 'application/json'])
            ->assertStatus(400)
            ->assertSeeText('The confirm field must be true or false.');
    }

    public function test_transaction_confirm_with_correct_data()
    {
        Sanctum::actingAs($this->wallet->user);

        $this->post('/api/transactions/' . $this->anotherTransaction->id . '/confirm', [
            'confirm' => true,
        ], ['Accept' => 'application/json'])
            ->assertStatus(200);
    }
}
