<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $userId = $request->user()->id;

        $items = Transaction::where('user_id', $userId)
            ->orderByDesc('created_at')
            ->get(['id', 'type', 'amount', 'created_at']);

        return response()->json(['items' => $items]);
    }

    public function store(Request $request)
    {
        $userId = $request->user()->id;

        $data = $request->validate([
            'type' => ['required', 'in:DEPOSIT,WITHDRAW'],
            'amount' => ['required', 'numeric', 'min:0', 'max:100000'],
        ]);

        DB::transaction(function () use ($userId, $data) {
            Transaction::create([
                'user_id' => $userId,
                'type' => $data['type'],
                'amount' => $data['amount'],
            ]);

            $this->recalculateBalance($userId);
        });

        $balance = Account::where('user_id', $userId)->value('balance');
        return response()->json(['balance' => (float) $balance]);
    }

    public function update(Request $request, $id)
    {
        $userId = $request->user()->id;

        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:0', 'max:100000'],
        ]);

        DB::transaction(function () use ($userId, $id, $data) {
            $tx = Transaction::where('user_id', $userId)->where('id', $id)->firstOrFail();
            $tx->amount = $data['amount'];
            $tx->save();

            $this->recalculateBalance($userId);
        });

        return response()->json(['ok' => true]);
    }

    public function destroy(Request $request, $id)
    {
        $userId = $request->user()->id;

        DB::transaction(function () use ($userId, $id) {
            Transaction::where('user_id', $userId)->where('id', $id)->firstOrFail()->delete();
            $this->recalculateBalance($userId);
        });

        return response()->json(['ok' => true]);
    }

    private function recalculateBalance(int $userId): void
    {
        $deposit = Transaction::where('user_id', $userId)->where('type', 'DEPOSIT')->sum('amount');
        $withdraw = Transaction::where('user_id', $userId)->where('type', 'WITHDRAW')->sum('amount');

        Account::updateOrCreate(
            ['user_id' => $userId],
            ['balance' => $deposit - $withdraw]
        );
    }
}
