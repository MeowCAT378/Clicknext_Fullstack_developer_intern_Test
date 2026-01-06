<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // ลบ user เก่าทั้งหมด
        User::query()->delete();

        // เพิ่ม user: Test
        User::create([
            'name' => 'Test',
            'email' => 'test@test.com',
            'password' => Hash::make('12345'),
        ]);

        // เพิ่ม user: Admin
        User::create([
            'name' => 'Admin',
            'email' => 'admin@admin.com',
            'password' => Hash::make('123456789'),
        ]);
    }
}
