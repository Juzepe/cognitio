<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Wallets\StoreWalletRequest;
use App\Http\Requests\Wallets\UpdateWalletRequest;
use App\Http\Requests\Wallets\WalletAuthorizationRequest;
use App\Models\Currency;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WalletController extends Controller
{
    private $walletFields = [
        'currency_id',
        'name',
        'amount',
        'is_active',
    ];

    public function index()
    {
        $wallets = Auth::user()->wallets()->get($this->walletFields);

        return [
            'status' => 'OK',
            'wallets' => $wallets,
        ];
    }

    public function create()
    {
        return [
            'status' => 'OK',
            'currencies' => Currency::all(['id', 'code', 'name']),
        ];
    }

    public function store(StoreWalletRequest $request)
    {
        Wallet::create($request->validated());

        return [
            'status' => 'OK',
        ];
    }

    public function show($id, WalletAuthorizationRequest $request)
    {
        return [
            'status' => 'OK',
            'wallet' => Wallet::find($id, $this->walletFields),
        ];
    }

    public function edit($id, WalletAuthorizationRequest $request)
    {
        return [
            'status' => 'OK',
            'wallet' => Wallet::find($id, $this->walletFields),
            'currencies' => Currency::all(['id', 'code', 'name']),
        ];
    }

    public function update(UpdateWalletRequest $request, $id)
    {
        Wallet::find($id)->update($request->validated());

        return [
            'status' => 'OK',
        ];
    }

    public function destroy($id, WalletAuthorizationRequest $request)
    {
        Wallet::find($id)->update(['is_active' => false]);

        return [
            'status' => 'OK',
        ];
    }

    public function active($id, WalletAuthorizationRequest $request)
    {
        Wallet::find($id)->update(['is_active' => true]);

        return [
            'status' => 'OK',
        ];
    }
}
