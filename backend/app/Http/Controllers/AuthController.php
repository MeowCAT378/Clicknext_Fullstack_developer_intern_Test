<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', $data['email'])->first();

        if (!$user || !Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['อีเมลหรือรหัสผ่านไม่ถูกต้อง'],
            ]);
        }

        $account = Account::firstOrCreate(['user_id' => $user->id], ['balance' => 0]);

        $user->tokens()->delete();
        $token = $user->createToken('web')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => ['id' => $user->id, 'email' => $user->email],
            'balance' => (float) $account->balance,
        ]);
    }

    public function me(Request $request)
    {
        $user = $request->user();
        $account = Account::firstOrCreate(['user_id' => $user->id], ['balance' => 0]);

        return response()->json([
            'user' => ['id' => $user->id, 'email' => $user->email],
            'balance' => (float) $account->balance,
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();
        return response()->json(['ok' => true]);
    }
}
