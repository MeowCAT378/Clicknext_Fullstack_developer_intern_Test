<?php

namespace App\Http\Controllers;

// ใช้งาน Model Account
use App\Models\Account;

// ใช้งาน Model User
use App\Models\User;

// ใช้งาน Request สำหรับรับข้อมูลจาก HTTP Request
use Illuminate\Http\Request;

// ใช้งาน Hash สำหรับตรวจสอบรหัสผ่าน
use Illuminate\Support\Facades\Hash;

// ใช้งาน ValidationException สำหรับส่ง error กลับเมื่อ login ไม่ผ่าน
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * ฟังก์ชันสำหรับ Login ผู้ใช้
     */
    public function login(Request $request)
    {
        // ตรวจสอบและ validate ข้อมูลที่รับมา
        $data = $request->validate([
            'email' => ['required', 'email'],      // ต้องเป็น email และห้ามว่าง
            'password' => ['required', 'string'],  // ต้องมี password และเป็น string
        ]);

        // ค้นหา user จาก email
        $user = User::where('email', $data['email'])->first();

        // ตรวจสอบว่ามี user หรือ password ถูกต้องหรือไม่
        if (!$user || !Hash::check($data['password'], $user->password)) {
            // ถ้าไม่ถูกต้อง ให้ throw validation error
            throw ValidationException::withMessages([
                'email' => ['อีเมลหรือรหัสผ่านไม่ถูกต้อง'],
            ]);
        }

        // สร้าง account ให้ user หากยังไม่มี (ตั้ง balance เริ่มต้นเป็น 0)
        $account = Account::firstOrCreate(
            ['user_id' => $user->id],
            ['balance' => 0]
        );

        // ลบ token เก่าทั้งหมดของ user (logout ทุก session)
        $user->tokens()->delete();

        // สร้าง token ใหม่สำหรับใช้งาน (Laravel Sanctum)
        $token = $user->createToken('web')->plainTextToken;

        // ส่งข้อมูลกลับเป็น JSON
        return response()->json([
            'token' => $token,                                     // token สำหรับ auth
            'user' => [
                'id' => $user->id,
                'email' => $user->email
            ],
            'balance' => (float) $account->balance,                // ยอดเงินของ account
        ]);
    }

    /**
     * ฟังก์ชันดึงข้อมูลผู้ใช้ปัจจุบัน (ต้อง login แล้ว)
     */
    public function me(Request $request)
    {
        // ดึง user จาก token ที่แนบมา
        $user = $request->user();

        // สร้าง account หากยังไม่มี
        $account = Account::firstOrCreate(
            ['user_id' => $user->id],
            ['balance' => 0]
        );

        // ส่งข้อมูล user และ balance กลับ
        return response()->json([
            'user' => [
                'id' => $user->id,
                'email' => $user->email
            ],
            'balance' => (float) $account->balance,
        ]);
    }

    /**
     * ฟังก์ชัน Logout
     */
    public function logout(Request $request)
    {
        // ลบ token user ปัจจุบัน
        $request->user()->tokens()->delete();

        // ส่งผลลัพธ์กลับ
        return response()->json(['ok' => true]);
    }
}
