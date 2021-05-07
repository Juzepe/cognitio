<?php

namespace App\Http\Requests\Wallets;

use App\Models\Wallet;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WalletAuthorizationRequest extends FormRequest
{
    public $wallet;

    public function __construct(Request $request)
    {
        $this->wallet = Wallet::find($request->wallet);

        parent::__construct();
    }

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->wallet && $this->wallet->user_id == Auth::id();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            //
        ];
    }

    protected function failedAuthorization()
    {
        response('This action is unauthorized.', '401')->throwResponse();
    }
}
