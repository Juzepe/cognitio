<?php

namespace App\Http\Requests\Transactions;

use App\Models\LatestCurrencyRate;
use App\Models\Wallet;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StoreTransactionRequest extends FormRequest
{
    public $walletFrom;
    public $walletTo;
    public $latestRate;

    public function __construct(Request $request)
    {
        $this->walletFrom = Wallet::find($request->wallet_from);
        $this->walletTo = Wallet::find($request->wallet_to);

        if ($this->walletFrom && $this->walletTo) {
            $this->latestRate = LatestCurrencyRate::where('currency_from', $this->walletFrom->currency_id)
                ->where('currency_to', $this->walletTo->currency_id)
                ->first();
        }

        parent::__construct();
    }

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return !$this->walletFrom || $this->walletFrom->user_id == Auth::id();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'wallet_from' => 'required|exists:wallets,id',
            'wallet_to' => 'required|exists:wallets,id',
            'amount' => 'required|numeric|min:1.00|max:10000.00',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if (isset($this->walletFrom) && $this->walletFrom->amount < request()->amount) {
                $validator->errors()->add('amount', 'You have not sufficient money for this transaction.');
            }

            if (
                !$this->latestRate
                && isset($this->walletFrom)
                && isset($this->walletTo)
                &&  $this->walletFrom->currency_id != $this->walletTo->currency_id
            ) {
                $validator->errors()->add('wallet_from', 'Making transaction is temporary unavailable.');
            }
        });
    }

    protected function failedAuthorization()
    {
        response('This action is unauthorized.', '401')->throwResponse();
    }

    protected function failedValidation(Validator $validator)
    {
        response()->json($validator->errors(), '400')->throwResponse();
    }
}
