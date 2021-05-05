<?php

namespace App\Http\Requests\Wallets;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;

class StoreWalletRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(Request $request)
    {
        return [
            'user_id' => 'required|exists:users,id|unique:wallets,user_id,NULL,id,currency_id,' . $request->currency_id,
            'currency_id' => 'required|exists:currencies,id',
            'name' => 'required',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        response()->json($validator->errors(), '400')->throwResponse();
    }
}
