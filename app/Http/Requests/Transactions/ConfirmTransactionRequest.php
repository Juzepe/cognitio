<?php

namespace App\Http\Requests\Transactions;

use App\Models\Transaction;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ConfirmTransactionRequest extends FormRequest
{
    public $transaction;

    public function __construct(Request $request)
    {
        $this->transaction = $request->transaction;

        parent::__construct();
    }
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(Request $request)
    {
        return $this->transaction->walletTo->user_id == Auth::id();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'confirm' => 'required|boolean',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if (in_array($this->transaction->transactionStatus->code, ['confirmed', 'reject'])) {
                $validator->errors()->add('transaction', 'The transaction is already finished.');
            }
        });
    }

    protected function failedAuthorization()
    {
        response('This action is unauthorized..', '401')->throwResponse();
    }

    protected function failedValidation(Validator $validator)
    {
        response()->json($validator->errors(), '400')->throwResponse();
    }
}
