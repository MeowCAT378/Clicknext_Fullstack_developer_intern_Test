<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransactionController extends Controller
{
    // แสดงรายการธุรกรรมทั้งหมดของผู้ใช้ที่ login อยู่
    public function index(Request $request)
    {
        // ดึง id ที่ login
        $userId = $request->user()->id;

        // ดึงรายการ user เรียงจากใหม่ไปเก่า
        $items = Transaction::where('user_id', $userId)
            ->orderByDesc('created_at')
            ->get(['id', 'type', 'amount', 'created_at']);

        // ส่งรายการธุรกรรมกลับเป็น JSON
        return response()->json(['items' => $items]);
    }

    // สร้างธุรกรรมใหม่ (ฝากเงิน / ถอนเงิน)
    public function store(Request $request)
    {
        // ดึง id ที่ login
        $userId = $request->user()->id;

        // ตรวจสอบข้อมูลที่ส่งมา
        $data = $request->validate([
            'type' => ['required', 'in:DEPOSIT,WITHDRAW'], // ประเภทต้องฝากหรือถอน
            'amount' => ['required', 'numeric', 'min:0', 'max:100000'], // จำนวนเงิน
        ]);

        // ใช้ DB transaction
        DB::transaction(function () use ($userId, $data) {

            // บันทึกยอดใหม่
            Transaction::create([
                'user_id' => $userId,
                'type' => $data['type'],
                'amount' => $data['amount'],
            ]);

            // คำนวณยอดเงินใหม่
            $this->recalculateBalance($userId);
        });

        // ดึง balance ล่าสุดของ user
        $balance = Account::where('user_id', $userId)->value('balance');

        // ส่งยอดเงินล่าสุดกลับ
        return response()->json(['balance' => (float) $balance]);
    }

    // แก้ไขจำนวนเงินที่มีอยู่
    public function update(Request $request, $id)
    {
        // ดึง id ที่ login
        $userId = $request->user()->id;

        // ตรวจสอบข้อมูลที่ส่งมา
        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:0', 'max:100000'], // จำนวนเงินใหม่
        ]);

        // ใช้ DB transaction
        DB::transaction(function () use ($userId, $id, $data) {

            // ค้นหาธุรกรรมตาม id
            $tx = Transaction::where('user_id', $userId)
                ->where('id', $id)
                ->firstOrFail();

            // อัปเดตจำนวนเงิน
            $tx->amount = $data['amount'];
            $tx->save();

            // คำนวณยอดเงินใหม่
            $this->recalculateBalance($userId);
        });

        // ส่งผลลัพธ์กลับ
        return response()->json(['ok' => true]);
    }

    // ลบธุรกรรม
    public function destroy(Request $request, $id)
    {
        // ดึง user id จากใช้ที่ login
        $userId = $request->user()->id;

        // ใช้ DB transaction
        DB::transaction(function () use ($userId, $id) {

            // ค้นหาและลบธุรกรรม
            Transaction::where('user_id', $userId)
                ->where('id', $id)
                ->firstOrFail()
                ->delete();

            // คำนวณยอดเงินใหม่หลังลบ
            $this->recalculateBalance($userId);
        });

        // ส่งผลลัพธ์
        return response()->json(['ok' => true]);
    }

    // คำนวณยอดเงินคงเหลือใหม่
    private function recalculateBalance(int $userId): void
    {
        // รวมยอดฝากทั้งหมด
        $deposit = Transaction::where('user_id', $userId)
            ->where('type', 'DEPOSIT')
            ->sum('amount');

        // รวมยอดถอนทั้งหมด
        $withdraw = Transaction::where('user_id', $userId)
            ->where('type', 'WITHDRAW')
            ->sum('amount');

        // อัปเดต
        Account::updateOrCreate(
            ['user_id' => $userId],
            ['balance' => $deposit - $withdraw]
        );
    }
}
