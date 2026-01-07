<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TransactionController;

// Public Routes (ไม่ต้อง Login)
// Route สำหรับ Login ผู้ใช้
Route::post('/auth/login', [AuthController::class, 'login']);


// Protected Routes (ต้อง Login ด้วย Sanctum)
// กลุ่ม route ที่ต้องผ่าน middleware auth:sanctum
Route::middleware('auth:sanctum')->group(function () {
    // ดึงข้อมูลผู้ใช้ที่ login อยู่
    Route::get('/me', [AuthController::class, 'me']);
    // Logout ผู้ใช้ (ลบ token)
    Route::post('/auth/logout', [AuthController::class, 'logout']);


    // Transaction Routes
    // ดึงรายการธุรกรรมทั้งหมดของ
    Route::get('/transactions', [TransactionController::class, 'index']);
    // สร้างยอดใหม่ (ฝาก / ถอน)
    Route::post('/transactions', [TransactionController::class, 'store']);
    // แก้ไขตาม id
    Route::put('/transactions/{id}', [TransactionController::class, 'update']);
    // ลบตาม id
    Route::delete('/transactions/{id}', [TransactionController::class, 'destroy']);
});
