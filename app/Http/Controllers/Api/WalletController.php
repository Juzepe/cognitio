<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Wallets\StoreWalletRequest;
use App\Http\Requests\Wallets\UpdateWalletRequest;
use App\Models\Currency;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WalletController extends Controller
{
    public function index()
    {
        $wallets = Wallet::where('user_id', Auth::id())
            ->with('currency:id,code,name')
            ->get([
                'currency_id',
                'name',
                'amount',
                'is_active',
            ]);

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

    public function show($id)
    {
        return [
            'status' => 'OK',
            'wallet' => $this->wallet($id),
        ];
    }

    public function edit($id)
    {
        return [
            'status' => 'OK',
            'wallet' => $this->wallet($id),
            'currencies' => Currency::all(['id', 'code', 'name']),
        ];
    }

    public function update(UpdateWalletRequest $request, $id)
    {
        $this->wallet($id)->update($request->validated());

        return [
            'status' => 'OK',
        ];
    }

    public function destroy($id)
    {
        $this->wallet($id)->update(['is_active' => false]);

        return [
            'status' => 'OK',
        ];
    }

    public function active($id)
    {
        $this->wallet($id)->update(['is_active' => true]);

        return [
            'status' => 'OK',
        ];
    }

    private function wallet($id)
    {
        return Wallet::where('user_id', Auth::id())
            ->where('id', $id)
            ->first([
                'id',
                'currency_id',
                'name',
                'amount',
                'is_active',
            ]);
    }
}
