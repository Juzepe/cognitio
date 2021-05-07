<?php

namespace Tests\Feature\Transactions;

use App\Models\Currency;
use App\Models\LatestCurrencyRate;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TransactionStoreValidationTest extends TestCase
{
    use RefreshDatabase;

    private $user;
    private $anotherUser;
    private $wallet;
    private $anotherUserWallet;

    public function setUp(): void
    {
        parent::setUp();

        $this->seed();

        $this->user = User::factory()->create();
        $this->anotherUser = User::factory()->create();
        $this->wallet = Wallet::factory()->create([
            'user_id' => $this->user->id,
            'currency_id' => Currency::idByCode("GEL"),
        ]);
        $this->anotherUserWallet = Wallet::factory()->create([
            'user_id' => $this->anotherUser->id,
            'currency_id' => Currency::idByCode("USD"),
        ]);
    }

    public function test_transaction_validations_unauthorized()
    {
        $this->post('/api/transactions', [], ['Accept' => 'application/json'])
            ->assertStatus(401);
    }

    public function test_transaction_validations_without_data()
    {
        Sanctum::actingAs($this->user);

        $this->post('/api/transactions', [], ['Accept' => 'application/json'])
            ->assertStatus(400)
            ->assertSeeText('The wallet from field is required.')
            ->assertSeeText('The wallet to field is required.')
            ->assertSeeText('The amount field is required.');
    }

    public function test_transaction_validations_with_incorrect_wallet_from()
    {
        Sanctum::actingAs($this->user);

        $this->post('/api/transactions', [
            'wallet_from' => 7,
        ], ['Accept' => 'application/json'])
            ->assertStatus(400)
            ->assertSeeText('The selected wallet from is invalid.');

        $this->post('/api/transactions', [
            'wallet_from' => $this->anotherUserWallet->id,
        ], ['Accept' => 'application/json'])
            ->assertStatus(401)
            ->assertSeeText('This action is unauthorized.');
    }

    public function test_transaction_validations_with_incorrect_wallet_to()
    {
        Sanctum::actingAs($this->user);

        $this->post('/api/transactions', [
            'wallet_to' => 7,
        ], ['Accept' => 'application/json'])
            ->assertStatus(400)
            ->assertSeeText('The selected wallet to is invalid.');
    }

    public function test_transaction_validations_when_the_rate_does_not_exist()
    {
        Sanctum::actingAs($this->user);

        $this->post('/api/transactions', [
            'wallet_from' => $this->wallet->id,
            'wallet_to' => $this->anotherUserWallet->id,
        ], ['Accept' => 'application/json'])
            ->assertStatus(400)
            ->assertSeeText('Making transaction is temporary unavailable.');
    }

    public function test_transaction_validations_with_incorrect_amount()
    {
        Sanctum::actingAs($this->user);

        $this->post('/api/transactions', [
            'amount' => 'test',
        ], ['Accept' => 'application/json'])
            ->assertStatus(400)
            ->assertSeeText('The amount must be a number.');

        $this->post('/api/transactions', [
            'amount' => 0.5,
        ], ['Accept' => 'application/json'])
            ->assertStatus(400)
            ->assertSeeText('The amount must be at least 1.00.');
    }

    public function test_transaction_validations_with_correct_data()
    {
        Sanctum::actingAs($this->user);

        LatestCurrencyRate::factory()->create([
            'currency_from' => $this->wallet->currency_id,
            'currency_to' => $this->anotherUserWallet->currency_id,
            'rate' => 0.29,
        ]);

        $this->post('/api/transactions', [
            'wallet_from' => $this->wallet->id,
            'wallet_to' => $this->anotherUserWallet->id,
            'amount' => 10,
        ], ['Accept' => 'application/json'])
            ->assertStatus(200);
    }
}
