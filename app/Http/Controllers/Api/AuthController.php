<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(RegisterRequest $request)
    {
        User::create(array_merge($request->validated(), [
            'password' => Hash::make($request->password),
        ]));

        return [
            'status' => 'OK',
        ];
    }

    public function login(LoginRequest $request)
    {
        if (!auth()->attempt($request->validated(), true)) {
            return response(['password' => 'The password or the email is incorrect.'], '400');
        }

        return [
            'status' => 'OK',
            'token' => Auth::user()->createToken('token')->plainTextToken,
        ];
    }
}
